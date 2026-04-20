<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\NotificationService;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    protected $order;

    public function __construct($order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $title = 'Order Placed Successfully';
        $body = 'Your order #' . $this->order->id . ' for ₹' . number_format($this->order->total_amount, 2) . ' has been placed.';
        
        // Trigger push notification if enabled
        $ns = new NotificationService();
        $ns->sendPushNotification($notifiable->id, $title, $body, [
            'type' => 'order_placed',
            'order_id' => $this->order->id
        ]);

        return [
            'message' => $body,
            'order_id' => $this->order->id,
            'link' => route('orders.index'),
        ];
    }
}
