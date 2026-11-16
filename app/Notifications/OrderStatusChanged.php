<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** ជូនដំណឹងអតិថិជន: approved | rejected | paid */
class OrderStatusChanged extends Notification
{
    public function __construct(public Order $order, public string $event)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $no  = $this->order->order_no;
        $msg = (new MailMessage)->greeting('សួស្តី '.$notifiable->name);

        return match ($this->event) {
            'approved' => $msg->subject("ការកម្មង់ $no ត្រូវបានអនុម័ត")
                ->line('ការកម្មង់របស់អ្នកត្រូវបានអនុម័តហើយ។ សូមទូទាត់ $'.number_format($this->order->total, 2).' តាម KHQR។')
                ->action('ទូទាត់ឥឡូវនេះ', route('orders.show', $this->order)),
            'rejected' => $msg->subject("ការកម្មង់ $no ត្រូវបានបដិសេធ")
                ->line('សូមអភ័យទោស ការកម្មង់របស់អ្នកត្រូវបានបដិសេធ។')
                ->line('មូលហេតុ៖ '.($this->order->reject_reason ?: 'មិនបានបញ្ជាក់'))
                ->action('មើលការកម្មង់', route('orders.show', $this->order)),
            default => $msg->subject("បានទទួលការទូទាត់សម្រាប់ $no")
                ->line('យើងខ្ញុំបានទទួលការទូទាត់របស់អ្នកហើយ។ សូមអរគុណ!')
                ->action('មើលការកម្មង់', route('orders.show', $this->order)),
        };
    }
}
