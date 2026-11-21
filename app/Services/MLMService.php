<?php

namespace App\Services;

use App\Models\User;
use App\Models\Commission;
use App\Models\BvCommission;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MLMService
{
    public function distributeJoiningCommissions($newUserId)
    {
        $settings = $this->getSettings();
        
        $current = $newUserId;
        $level = 1;
        
        while ($level <= 5) {
            $ref = DB::table('referrals')->where('user_id', $current)->first();
            if (!$ref || !$ref->parent_id) {
                break;
            }
            
            $uplineId = $ref->parent_id;
            $uplineUser = User::find($uplineId);
            
            // Prevent duplicate commissions for same user joining
            $exists = Commission::where('from_user_id', $newUserId)
                ->where('user_id', $uplineId)
                ->where('type', 'joining')
                ->exists();

            if (!$exists && $uplineUser) {
                $amountKey = 'joining_commission_level_' . $level;
                $amount = (float) ($settings[$amountKey] ?? 0);
                
                if ($amount > 0) {
                    if ($uplineUser->status === 'active') {
                        $this->creditCommission($uplineId, $newUserId, null, $level, $amount, 'joining');
                    } else {
                        // Skip commission for inactive user but log it
                        $this->creditCommission($uplineId, $newUserId, null, $level, 0, 'joining', 'Commission skipped as not active user');
                    }
                }
            }
            
            $current = $uplineId;
            $level++;
        }
    }

    public function distributeOrderCommissions($order, ?float $customAmount = null, ?float $customBv = null)
    {
        $settings = $this->getSettings();
        $buyerId = $order->user_id;

        // Determine upfront basis for amount and BV
        if ($customAmount !== null) {
            $amountBasis = (float) $customAmount;
            $bvBasis = $customBv !== null ? (float) $customBv : (($order->total_amount > 0 && $amountBasis > 0) ? round((float) $order->total_bv * ($amountBasis / (float) $order->total_amount), 4) : 0.0);
        } elseif ($order->paid_amount !== null) {
            $amountBasis = (float) $order->paid_amount;
            $bvBasis = ($order->total_amount > 0 && $amountBasis > 0) ? round((float) $order->total_bv * ($amountBasis / (float) $order->total_amount), 4) : 0.0;
        } elseif ($order->payment_method === 'main_wallet') {
            $amountBasis = (float) $order->total_amount;
            $bvBasis = (float) $order->total_bv;
        } elseif ($order->payment_method === 'credit_wallet') {
            // Pure credit orders defer 100% of commission to EMI repayments
            $amountBasis = 0.0;
            $bvBasis = 0.0;
        } elseif ($order->payment_method === 'split') {
            // Fallback for split orders without explicit paid_amount
            $creditTotal = \App\Models\EmiSchedule::where('order_id', $order->id)->sum('installment_amount');
            $amountBasis = max(0.0, (float) $order->total_amount - (float) $creditTotal);
            $bvBasis = ($order->total_amount > 0 && $amountBasis > 0) ? round((float) $order->total_bv * ($amountBasis / (float) $order->total_amount), 4) : 0.0;
        } else {
            $amountBasis = (float) $order->total_amount;
            $bvBasis = (float) $order->total_bv;
        }

        // If no cash was paid upfront (e.g. 100% credit), defer commission to EMI repayment
        if ($amountBasis <= 0 && $bvBasis <= 0) {
            return;
        }

        $current = $buyerId;
        $level = 1;

        while ($level <= 5) {
            $ref = DB::table('referrals')->where('user_id', $current)->first();
            if (!$ref || !$ref->parent_id) {
                break;
            }

            $uplineId = $ref->parent_id;
            $uplineUser = User::find($uplineId);

            if ($uplineUser) {
                $isActive = ($uplineUser->status === 'active');

                // 1. Repurchase Commission (Percentage of cash/upfront order amount)
                $percKey = 'repurchase_commission_level_' . $level;
                $percentage = (float) ($settings[$percKey] ?? 0);
                $repurchaseCommission = ($amountBasis * $percentage) / 100;

                if ($repurchaseCommission > 0) {
                    $note = $isActive ? ($order->payment_method === 'split' ? 'Split payment upfront cash commission for Order #' . $order->id : null) : 'Commission skipped as not active user';
                    $this->creditCommission($uplineId, $buyerId, $order->id, $level, $isActive ? $repurchaseCommission : 0, 'repurchase', $note);
                }

                // 2. BV Commission (Rate per BV point on upfront BV)
                $rateKey = 'order_commission_level_' . $level;
                $rate = (float) ($settings[$rateKey] ?? 0);
                $bvCommission = $bvBasis * $rate;

                if ($bvCommission > 0) {
                    $bvNote = $isActive ? ($order->payment_method === 'split' ? 'Split payment upfront BV commission for Order #' . $order->id : null) : 'Commission skipped as not active user';
                    $this->creditCommission($uplineId, $buyerId, $order->id, $level, $isActive ? $bvCommission : 0, 'bv', $bvNote);
                }
            }

            $current = $uplineId;
            $level++;
        }
    }

    public function distributeEmiRepaymentCommissions(\App\Models\EmiSchedule $emi): bool
    {
        if ($emi->commission_distributed) {
            return false;
        }

        $order = $emi->order;
        if (!$order || in_array($order->status, ['cancelled', 'returned', 'failed'])) {
            return false;
        }

        $settings = $this->getSettings();
        $installmentAmount = (float) $emi->installment_amount;
        if ($installmentAmount <= 0) {
            return false;
        }

        // Calculate BV proportion for this EMI
        $emiBv = ($order->total_amount > 0 && (float) $order->total_bv > 0)
            ? round(((float) $order->total_bv * ($installmentAmount / (float) $order->total_amount)), 4)
            : 0.0;

        $buyerId = $emi->user_id;
        $current = $buyerId;
        $level = 1;

        while ($level <= 5) {
            $ref = DB::table('referrals')->where('user_id', $current)->first();
            if (!$ref || !$ref->parent_id) {
                break;
            }

            $uplineId = $ref->parent_id;
            $uplineUser = User::find($uplineId);

            if ($uplineUser) {
                $isActive = ($uplineUser->status === 'active');

                // 1. Repurchase Commission for this EMI installment
                $percKey = 'repurchase_commission_level_' . $level;
                $percentage = (float) ($settings[$percKey] ?? 0);
                $repurchaseCommission = ($installmentAmount * $percentage) / 100;

                if ($repurchaseCommission > 0) {
                    $note = $isActive 
                        ? 'Repurchase commission for EMI #' . $emi->id . ' repayment (Order #' . $order->id . ')' 
                        : 'Commission skipped as not active user';
                    $this->creditCommission($uplineId, $buyerId, $order->id, $level, $isActive ? $repurchaseCommission : 0, 'repurchase', $note);
                }

                // 2. BV Commission for this EMI installment
                $rateKey = 'order_commission_level_' . $level;
                $rate = (float) ($settings[$rateKey] ?? 0);
                $bvCommission = $emiBv * $rate;

                if ($bvCommission > 0) {
                    $bvNote = $isActive 
                        ? 'BV commission for EMI #' . $emi->id . ' repayment (Order #' . $order->id . ')' 
                        : 'Commission skipped as not active user';
                    $this->creditCommission($uplineId, $buyerId, $order->id, $level, $isActive ? $bvCommission : 0, 'bv', $bvNote);
                }
            }

            $current = $uplineId;
            $level++;
        }

        $emi->commission_distributed = true;
        $emi->save();

        return true;
    }

    public function reverseOrderCommissions($order)
    {
        $settings = $this->getSettings();
        $bvRate = (float)($settings['bv_conversion_rate'] ?? 1.0);

        DB::transaction(function () use ($order, $bvRate) {
            // 1. Handle regular commissions
            $commissions = Commission::where('order_id', $order->id)->get();
            foreach ($commissions as $comm) {
                $this->processCommissionReversal($comm, $order, $bvRate);
            }

            // 2. Handle BV commissions
            $bvCommissions = BvCommission::where('order_id', $order->id)->get();
            foreach ($bvCommissions as $bvComm) {
                $this->processCommissionReversal($bvComm, $order, $bvRate, true);
            }
        });
    }

    protected function processCommissionReversal($comm, $order, $bvRate, $isBvTable = false)
    {
        if ($comm->amount > 0 && $comm->status !== 'reversed') {
            $wallet = Wallet::where('user_id', $comm->user_id)->first();
            if ($wallet) {
                $oldStatus = $comm->status;
                $amountToDeduct = $comm->amount;
                $isWithdrawn = ($oldStatus === 'withdrawn');

                // Determine if it's BV type (either from type column or from being in the BvCommission table)
                $isBvType = $isBvTable || ($comm->type === 'bv');
                $referencePrefix = $isBvTable ? 'bv_reversal:' : 'reversal:';

                if ($isBvType) {
                    if ($isWithdrawn) {
                        // Deduct cash equivalent from main balance
                        $cashValue = $amountToDeduct * $bvRate;
                        $wallet->main_balance -= $cashValue;
                        
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'debit',
                            'source' => 'bv_reversal',
                            'amount' => $cashValue,
                            'reference_id' => $referencePrefix . $comm->id,
                            'description' => 'BV reversal for Order #' . $order->id . ' (Converted points recovered)'
                        ]);
                    } else {
                        $wallet->earning_balance -= $amountToDeduct;
                    }
                } else {
                    // joining or repurchase: if already withdrawn, deduct from main balance
                    if ($isWithdrawn) {
                        $wallet->main_balance -= $amountToDeduct;
                        
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'debit',
                            'source' => $comm->type . '_reversal',
                            'amount' => $amountToDeduct,
                            'reference_id' => $referencePrefix . $comm->id,
                            'description' => 'Commission reversal for Order #' . $order->id . ' (Withdrawn funds recovered)'
                        ]);
                    } else {
                        $wallet->commission_balance -= $amountToDeduct;
                    }
                }
                
                $wallet->save();
                $comm->update(['status' => 'reversed']);
            }
        }
    }

    protected function creditCommission($uplineId, $fromUserId, $orderId, $level, $amount, $type, $note = null)
    {
        $settings = $this->getSettings();
        $lockDays = (int) ($settings['commission_lock_period_days'] ?? 0);
        
        // If lockDays is 0, it's withdrawable immediately.
        // Otherwise, add the specified number of days.
        $withdrawableAt = $lockDays > 0 
            ? \Illuminate\Support\Carbon::now()->addDays($lockDays)->startOfDay() 
            : \Illuminate\Support\Carbon::now();

        DB::transaction(function () use ($uplineId, $fromUserId, $orderId, $level, $amount, $type, $note, $withdrawableAt) {
            if ($type === 'bv') {
                BvCommission::create([
                    'user_id' => $uplineId,
                    'from_user_id' => $fromUserId,
                    'order_id' => $orderId,
                    'level' => $level,
                    'amount' => $amount,
                    'status' => 'pending',
                    'withdrawable_at' => $withdrawableAt,
                    'note' => $note
                ]);
            } else {
                Commission::create([
                    'user_id' => $uplineId,
                    'from_user_id' => $fromUserId,
                    'order_id' => $orderId,
                    'level' => $level,
                    'amount' => $amount,
                    'status' => 'pending',
                    'withdrawable_at' => $withdrawableAt,
                    'type' => $type,
                    'note' => $note
                ]);
            }

            // Only update wallet and create transaction if amount is > 0
            if ($amount > 0) {
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $uplineId],
                    ['main_balance' => 0, 'commission_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]
                );

                // Add to the appropriate balance based on commission type
                if ($type === 'bv') {
                    $wallet->earning_balance += $amount;
                } else {
                    // joining or repurchase goes to commission_balance now
                    $wallet->commission_balance += $amount;
                }
                $wallet->save();
            }
        });
    }

    public function generateEmiSchedules($user, $order, ?float $customAmount = null)
    {
        $settings = $this->getSettings();
        $emiAmount = (float) ($settings['default_emi_amount'] ?? 500);
        $interval = (int) ($settings['emi_frequency'] ?? 7);
        $total = $customAmount !== null ? $customAmount : $order->total_amount;
        
        $remainingBalance = $total;
        $i = 1;
        
        while ($remainingBalance > 0) {
            $installment = min($remainingBalance, $emiAmount);
            $due = \Illuminate\Support\Carbon::now()->addDays($interval * $i);
            
            \App\Models\EmiSchedule::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'total_amount' => $total,
                'installment_amount' => $installment,
                'interval_days' => $interval,
                'due_date' => $due,
                'status' => 'pending'
            ]);
            
            $remainingBalance -= $installment;
            $i++;
        }
    }

    public function getSettings()
    {
        $settingsFile = 'settings.json';
        if (!\Illuminate\Support\Facades\Storage::disk('local')->exists($settingsFile)) {
            return [
                'site_name' => config('app.name', 'MLM App'),
                'contact_email' => 'admin@example.com',
                'currency' => 'INR',
                'min_withdrawal' => 500,
                'maintenance_mode' => 'off',
                'registration_enabled' => 'on',
                'enable_bv_commission' => 'on',
                'default_order_status' => 'processing',
                'default_emi_amount' => 500,
                'emi_frequency' => 7,
                'late_penalty_amount' => 80,
                'joining_commission_level_1' => 100,
                'joining_commission_level_2' => 50,
                'joining_commission_level_3' => 30,
                'joining_commission_level_4' => 20,
                'joining_commission_level_5' => 10,
                'repurchase_commission_level_1' => 20,
                'repurchase_commission_level_2' => 10,
                'repurchase_commission_level_3' => 5,
                'repurchase_commission_level_4' => 3,
                'repurchase_commission_level_5' => 2,
                'order_commission_level_1' => 2.0,
                'order_commission_level_2' => 1.0,
                'order_commission_level_3' => 0.3,
                'order_commission_level_4' => 0.2,
                'order_commission_level_5' => 0.1,
                'commission_lock_period_days' => 30,
                'min_commission_withdrawal' => 500,
                'commission_withdrawal_tds_percent' => 5,
                'commission_withdrawal_service_charge' => 5,
                'bv_conversion_rate' => 1.0,
                'min_bv_withdrawal' => 100,
            ];
        }
        return json_decode(\Illuminate\Support\Facades\Storage::disk('local')->get($settingsFile), true);
    }
}
