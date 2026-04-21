<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
    protected $description = 'Check for overdue EMIs and apply penalties';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mlmService = new \App\Services\MLMService();
        $settings = $mlmService->getSettings();
        $penaltyAmount = (float) ($settings['late_penalty_amount'] ?? 80);

        $overdueEmis = \App\Models\EmiSchedule::where('status', 'pending')
            ->where('due_date', '<', now()->toDateString())
            ->get();

        $this->info("Found " . $overdueEmis->count() . " overdue EMIs.");

        foreach ($overdueEmis as $emi) {
            \Illuminate\Support\Facades\DB::transaction(function () use ($emi, $penaltyAmount) {
                // Check if penalty already exists for this EMI to avoid duplicates (Safety check)
                $exists = \App\Models\Penalty::where('emi_schedule_id', $emi->id)->exists();
                if ($exists) {
                    // If penalty exists but EMI is still pending, just mark EMI as overdue and skip
                    $emi->status = 'overdue';
                    $emi->save();
                    return;
                }

                // Update EMI status
                $emi->status = 'overdue';
                $emi->save();

                $user = \App\Models\User::find($emi->user_id);
                $wallet = $user ? $user->wallet : null;
                $status = 'unpaid';

                // Automatic Deduction Logic
                if ($wallet && $wallet->main_balance >= $penaltyAmount) {
                    $wallet->main_balance -= $penaltyAmount;
                    $wallet->save();

                    \App\Models\WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'type' => 'debit',
                        'source' => 'penalty',
                        'amount' => $penaltyAmount,
                        'reference_id' => 'penalty:' . $emi->id,
                        'description' => 'Auto-deducted Penalty for Overdue EMI #' . $emi->id
                    ]);

                    $status = 'paid';
                }

                $penalty = \App\Models\Penalty::create([
                    'user_id' => $emi->user_id,
                    'emi_schedule_id' => $emi->id,
                    'amount' => $penaltyAmount,
                    'status' => $status
                ]);

                // Send Notification
                if ($user) {
                    $user->notify(new \App\Notifications\PenaltyLeviedNotification($penalty));
                }

                if ($status === 'paid') {
                    $this->line("Auto-deducted penalty of {$penaltyAmount} from User #{$emi->user_id} for EMI #{$emi->id}");
                } else {
                    $this->line("Applied unpaid penalty of {$penaltyAmount} to User #{$emi->user_id} (Insufficient Balance)");
                }
            });
        }

        $this->info("Penalty application completed.");
    }
}
