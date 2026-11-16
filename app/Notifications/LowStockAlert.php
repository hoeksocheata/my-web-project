<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class LowStockAlert extends Notification
{
    public function __construct(public Collection $books)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('⚠️ សៀវភៅជិតអស់ស្តុក ('.$this->books->count().')')
            ->greeting('សួស្តី '.$notifiable->name)
            ->line('សៀវភៅខាងក្រោមត្រូវការបំពេញស្តុក៖');

        foreach ($this->books as $b) {
            $mail->line("• {$b->title} — នៅសល់ {$b->stock} ក្បាល");
        }

        return $mail->action('បំពេញស្តុក', route('purchases.index'));
    }
}
