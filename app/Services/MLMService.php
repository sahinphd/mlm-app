<?php

namespace App\Services;

use App\Models\User;
use App\Models\Commission;
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

            if (!$exists && $uplineUser && $uplineUser->status === 'active') {
                $amountKey = 'joining_commission_level_' . $level;
                $amount = (float) ($settings[$amountKey] ?? 0);
                
                if ($amount > 0) {
                    $this->creditCommission($uplineId, $newUserId, null, $level, $amount, 'joining');
                }
            }
            
            $current = $uplineId;
            $level++;
        }
    }

    public function distributeOrderCommissions($order)
    {
        $settings = $this->getSettings();
        $totalBv = $order->total_bv;
        $totalAmount = $order->total_amount;
        $buyerId = $order->user_id;
        
        $current = $buyerId;
        $level = 1;

        while ($level <= 5) {
            $ref = DB::table('referrals')->where('user_id', $current)->first();
            if (!$ref || !$ref->parent_id) {
                break;
            }

            $uplineId = $ref->parent_id;
            $uplineUser = User::find($uplineId);

            if ($uplineUser && $uplineUser->status === 'active') {
                // 1. BV-based commission
                $rateKey = 'order_commission_level_' . $level;
                $rate = (float) ($settings[$rateKey] ?? 0);
                $bvAmount = $totalBv * $rate;

                // 2. Percentage-based commission
                $percKey = 'repurchase_commission_level_' . $level;
                $percentage = (float) ($settings[$percKey] ?? 0);
                $percAmount = ($totalAmount * $percentage) / 100;

                $totalCommission = $bvAmount + $percAmount;

                if ($totalCommission > 0) {
                    $this->creditCommission($uplineId, $buyerId, $order->id, $level, $totalCommission, 'repurchase');
                }
            }

            $current = $uplineId;
            $level++;
        }
    }

    protected function creditCommission($uplineId, $fromUserId, $orderId, $level, $amount, $type)
    {
        DB::transaction(function () use ($uplineId, $fromUserId, $orderId, $level, $amount, $type) {
            $comm = Commission::create([
                'user_id' => $uplineId,
                'from_user_id' => $fromUserId,
                'order_id' => $orderId,
                'level' => $level,
                'amount' => $amount,
                'type' => $type
            ]);

            $wallet = Wallet::firstOrCreate(
                ['user_id' => $uplineId],
                ['main_balance' => 0, 'earning_balance' => 0, 'credit_balance' => 0]
            );

            $wallet->earning_balance += $amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'credit',
                'source' => 'commission',
                'amount' => $amount,
                'reference_id' => 'commission:' . $comm->id,
                'description' => ucfirst($type) . ' commission (Level ' . $level . ') from Order/User #' . ($orderId ?? $fromUserId)
            ]);
        });
    }

    protected function getSettings()
    {
        $settingsFile = 'settings.json';
        if (!Storage::disk('local')->exists($settingsFile)) {
            return [
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
            ];
        }
        return json_decode(Storage::disk('local')->get($settingsFile), true);
    }
}
