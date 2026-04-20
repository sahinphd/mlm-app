<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\NotificationService;

class PenaltyLeviedNotification extends Notification
{
    use Queueable;

    protected $penalty;

    public function __construct($penalty)
    {
        $this->penalty = $penalty;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = 'Late Penalty Applied';
        $body = 'A penalty of ₹' . number_format($this->penalty->amount, 2) . ' has been applied due to an overdue EMI.';
        
        // Trigger push notification if enabled
        $ns = new NotificationService();
        $ns->sendPushNotification($notifiable->id, $title, $body, [
            'type' => 'penalty_levied',
            'penalty_id' => $this->penalty->id
        ]);

        return [
            'message' => $body,
            'penalty_id' => $this->penalty->id,
            'link' => route('credit.emis'),
        ];
    }
}
