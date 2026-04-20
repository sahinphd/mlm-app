<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\NotificationService;

class UpcomingEmiNotification extends Notification
{
    use Queueable;

    protected $emi;

    public function __construct($emi)
    {
        $this->emi = $emi;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = 'Upcoming EMI Reminder';
        $body = 'Your EMI of ₹' . number_format($this->emi->installment_amount, 2) . ' is due on ' . \Carbon\Carbon::parse($this->emi->due_date)->format('M d, Y');
        
        // Trigger push notification if enabled
        $ns = new NotificationService();
        $ns->sendPushNotification($notifiable->id, $title, $body, [
            'type' => 'emi_reminder',
            'emi_id' => $this->emi->id
        ]);

        return [
            'message' => $body,
            'emi_id' => $this->emi->id,
            'link' => route('credit.emis'),
        ];
    }
}
