<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\NotificationService;

class OrderUpdateNotification extends Notification
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
        $status = ucfirst($this->order->status);
        $title = 'Order Status Updated';
        $body = "Your order #{$this->order->id} status has been updated to: {$status}";
        
        // Trigger push notification if enabled
        $ns = new NotificationService();
        $ns->sendPushNotification($notifiable->id, $title, $body, [
            'type' => 'order_update',
            'order_id' => $this->order->id,
            'status' => $this->order->status
        ]);

        return [
            'message' => $body,
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'link' => route('orders.index'),
        ];
    }
}
