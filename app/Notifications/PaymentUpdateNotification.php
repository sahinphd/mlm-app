<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Storage;

class PaymentUpdateNotification extends Notification
{
    use Queueable;

    protected $paymentRequest;

    public function __construct($paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $status = ucfirst($this->paymentRequest->status);
        $title = "Payment Request {$status}";
        $currency = $this->getCurrency();
        $amount = number_format($this->paymentRequest->amount, 2);
        
        $body = "Your payment request for {$currency} {$amount} has been {$this->paymentRequest->status}.";
        if ($this->paymentRequest->admin_note) {
            $body .= " Note: " . $this->paymentRequest->admin_note;
        }
        
        // Trigger push notification if enabled
        $ns = new NotificationService();
        $ns->sendPushNotification($notifiable->id, $title, $body, [
            'type' => 'payment_update',
            'request_id' => $this->paymentRequest->id,
            'status' => $this->paymentRequest->status
        ]);

        return [
            'message' => $body,
            'payment_request_id' => $this->paymentRequest->id,
            'status' => $this->paymentRequest->status,
            'link' => route('wallet.history'),
        ];
    }

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
