<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

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
        $currency = $this->getCurrency();
        $message = 'New wallet top-up request of ' . $currency . ' ' . number_format($this->paymentRequest->amount, 2) . ' from ' . $this->paymentRequest->user->name;

        // Trigger push notification if enabled
        $ns = new \App\Services\NotificationService();
        $ns->sendPushNotification($notifiable->id, 'New Payment Request', $message, [
            'type' => 'payment_request',
            'request_id' => $this->paymentRequest->id
        ]);

        return [
            'message' => $message,
            'payment_request_id' => $this->paymentRequest->id,
            'link' => route('payments.admin'),
        ];
    }

    /**
     * Read currency from settings.json stored on local disk.
     */
    protected function getCurrency(): string
    {
        $file = 'settings.json';
        if (!Storage::disk('local')->exists($file)) {
            return 'INR';
        }

        $settings = json_decode(Storage::disk('local')->get($file), true);
        return $settings['currency'] ?? 'INR';
    }
}
