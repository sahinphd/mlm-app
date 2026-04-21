<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\EmiSchedule;
use App\Models\Penalty;
use App\Models\WalletTransaction;
use App\Models\CreditTransaction;
use App\Services\MLMService;
use Illuminate\Support\Facades\DB;
use App\Notifications\PenaltyLeviedNotification;

class ApplyEmiPenalties extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'emi:apply-penalties';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-pay EMIs on due date and apply penalties for overdue ones';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mlmService = new MLMService();
        $settings = $mlmService->getSettings();
        $penaltyAmount = (float) ($settings['late_penalty_amount'] ?? 80);
        $today = now()->toDateString();

        // Get pending EMIs due today or earlier, AND all overdue EMIs
        $emisToProcess = EmiSchedule::whereIn('status', ['pending', 'overdue'])
            ->where('due_date', '<=', $today)
            ->get();

        $this->info("Found " . $emisToProcess->count() . " EMIs to process (Due today or Overdue).");

        foreach ($emisToProcess as $emi) {
            DB::transaction(function () use ($emi, $penaltyAmount, $today) {
                $user = User::find($emi->user_id);
                if (!$user) return;

                $wallet = $user->wallet;
                $ca = $user->creditAccount;

                // Determine if it's already overdue (past the due date)
                $isOverdue = $emi->status === 'overdue' || $emi->due_date < $today;
                
                $penalty = null;
                if ($isOverdue) {
                    // Safety: find or create penalty record for this specific EMI
                    $penalty = Penalty::firstOrCreate(
                        ['emi_schedule_id' => $emi->id],
                        [
                            'user_id' => $emi->user_id,
                            'amount' => $penaltyAmount,
                            'status' => 'unpaid'
                        ]
                    );

                    // If it was just created (meaning EMI just transitioned to overdue), send notification
                    if ($penalty->wasRecentlyCreated) {
                        $user->notify(new PenaltyLeviedNotification($penalty));
                        $this->line("Applied unpaid penalty of {$penaltyAmount} to User #{$emi->user_id} for EMI #{$emi->id}");
                    }
                    
                    if ($emi->status !== 'overdue') {
                        $emi->status = 'overdue';
                        $emi->save();
                    }
                }

                // --- Auto-Payment Logic ---
                $emiAmount = (float) $emi->installment_amount;
                $unpaidPenaltyAmount = 0;

                if ($penalty && $penalty->status === 'unpaid') {
                    $unpaidPenaltyAmount = (float) $penalty->amount;
                }

                $totalToDeduct = $emiAmount + $unpaidPenaltyAmount;

                if ($wallet && $wallet->main_balance >= $totalToDeduct) {
                    // 1. Deduct from wallet
                    $wallet->main_balance -= $totalToDeduct;
                    $wallet->save();

                    // 2. Record Wallet Transaction for EMI
                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'source' => 'emi',
                        'amount' => $emiAmount,
                        'reference_id' => 'emi:' . $emi->id,
                        'description' => 'Auto-deducted EMI Payment for Order #' . $emi->order_id
                    ]);

                    // 3. Record Wallet Transaction for Penalty if any
                    if ($unpaidPenaltyAmount > 0) {
                        WalletTransaction::create([
                            'wallet_id' => $wallet->id,
                            'type' => 'debit',
                            'source' => 'penalty',
                            'amount' => $unpaidPenaltyAmount,
                            'reference_id' => 'penalty:' . $penalty->id,
                            'description' => 'Auto-deducted Penalty for Overdue EMI #' . $emi->id
                        ]);

                        $penalty->status = 'paid';
                        $penalty->save();
                    }

                    // 4. Update Credit Account
                    if ($ca) {
                        $ca->used_credit = max(0, $ca->used_credit - $emiAmount);
                        $ca->available_credit = min($ca->credit_limit, $ca->available_credit + $emiAmount);
                        $ca->save();

                        CreditTransaction::create([
                            'credit_account_id' => $ca->id,
                            'type' => 'credit',
                            'amount' => $emiAmount,
                            'source' => 'repayment',
                            'reference_id' => 'emi:' . $emi->id,
                            'description' => 'Auto EMI Repayment'
                        ]);
                    }

                    // 5. Finalize EMI Status
                    $emi->status = 'paid';
                    $emi->save();

                    $this->line("Successfully auto-paid EMI #{$emi->id} for User #{$emi->user_id}. Total: ₹{$totalToDeduct}");
                } else {
                    $this->line("Insufficient balance (₹".($wallet->main_balance ?? 0).") to auto-pay EMI #{$emi->id} for User #{$emi->user_id}. Needed: ₹{$totalToDeduct}");
                }
            });
        }

        $this->info("EMI processing completed.");
    }
}
