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
                // Update EMI status
                $emi->status = 'overdue';
                $emi->save();

                // Check if penalty already exists for this EMI to avoid duplicates
                $exists = \App\Models\Penalty::where('emi_schedule_id', $emi->id)->exists();

                if (!$exists) {
                    $penalty = \App\Models\Penalty::create([
                        'user_id' => $emi->user_id,
                        'emi_schedule_id' => $emi->id,
                        'amount' => $penaltyAmount,
                        'status' => 'unpaid'
                    ]);

                    // Send Notification
                    $user = \App\Models\User::find($emi->user_id);
                    if ($user) {
                        $user->notify(new \App\Notifications\PenaltyLeviedNotification($penalty));
                    }

                    $this->line("Applied penalty of {$penaltyAmount} to User #{$emi->user_id} for EMI #{$emi->id}");
                }
            });
        }

        $this->info("Penalty application completed.");
    }
}
