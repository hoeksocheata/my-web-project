<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** ជូនដំណឹង Admin ពេលមានការកម្មង់ថ្មី */
class NewOrderPlaced extends Notification
{
    public function __construct(public Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ការកម្មង់ថ្មី '.$this->order->order_no)
            ->greeting('សួស្តី '.$notifiable->name)
            ->line($this->order->user->name.' បានកម្មង់សៀវភៅ ចំនួន '.$this->order->items()->sum('quantity').' ក្បាល')
            ->line('សរុប៖ $'.number_format($this->order->total, 2))
            ->action('ពិនិត្យការកម្មង់', route('admin.orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'icon'     => 'bi-bag-plus',
            'title'    => 'ការកម្មង់ថ្មី '.$this->order->order_no,
            'body'     => $this->order->user->name.' · $'.number_format($this->order->total, 2),
            'url'      => route('admin.orders.show', $this->order),
            'order_id' => $this->order->id,
        ];
    }
}
