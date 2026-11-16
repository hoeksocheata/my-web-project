<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

/** ជូនដំណឹង Admin ពេលអតិថិជនបញ្ជូនភស្តុតាងទូទាត់ */
class PaymentSubmitted extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'     => 'bi-cash-coin',
            'title'    => 'មានអ្នកបង់លុយ '.$this->order->order_no,
            'body'     => $this->order->user->name.' · $'.number_format($this->order->total, 2).' — សូមផ្ទៀងផ្ទាត់',
            'url'      => route('admin.orders.show', $this->order),
            'order_id' => $this->order->id,
        ];
    }
}
