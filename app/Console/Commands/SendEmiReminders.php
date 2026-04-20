<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EmiSchedule;
use App\Models\User;
use App\Notifications\UpcomingEmiNotification;
use Carbon\Carbon;

class SendEmiReminders extends Command
{
    protected $signature = 'emi:send-reminders';
    protected $description = 'Send notifications for EMIs due in 2 days';

    public function handle()
    {
        $dueDate = Carbon::now()->addDays(2)->toDateString();
        
        $upcomingEmis = EmiSchedule::where('status', 'pending')
            ->where('due_date', $dueDate)
            ->get();

        $this->info("Found " . $upcomingEmis->count() . " EMIs due on " . $dueDate);

        foreach ($upcomingEmis as $emi) {
            $user = User::find($emi->user_id);
            if ($user) {
                $user->notify(new UpcomingEmiNotification($emi));
                $this->line("Sent reminder to User #{$user->id} for EMI #{$emi->id}");
            }
        }

        $this->info("Reminder process completed.");
    }
}
