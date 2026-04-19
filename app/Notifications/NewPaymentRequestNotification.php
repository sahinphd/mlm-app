<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewPaymentRequestNotification extends Notification
{
    use Queueable;

    protected $paymentRequest;

    /**
     * Create a new notification instance.
     */
    public function __construct($paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'New wallet top-up request of ৳' . number_format($this->paymentRequest->amount, 2) . ' from ' . $this->paymentRequest->user->name,
            'payment_request_id' => $this->paymentRequest->id,
            'link' => route('payments.admin'),
        ];
    }
}
