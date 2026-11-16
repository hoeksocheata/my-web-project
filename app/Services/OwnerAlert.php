<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * ជូនដំណឹងម្ចាស់ហាង / Admin ភ្លាមៗ ពេលមានការកម្មង់ ឬការបង់ប្រាក់៖
 *  1) ការជូនដំណឹងក្នុងប្រព័ន្ធ (រូបកណ្ដឹង + សំឡេង, ធ្វើបច្ចុប្បន្នភាពស្វ័យប្រវត្តិ) និងអ៊ីមែល (បើមាន)
 *  2) Telegram (ស្រេចចិត្ត) — កំណត់ TELEGRAM_BOT_TOKEN និង TELEGRAM_CHAT_ID ក្នុង .env
 * បរាជ័យមួយណា មិនធ្វើឱ្យការកម្មង់របស់អតិថិជនបរាជ័យឡើយ។
 */
class OwnerAlert
{
    public function send(Notification $notification, string $telegramText): void
    {
        rescue(fn () => NotificationFacade::send(User::where('role', 'admin')->get(), $notification));
        $this->telegram($telegramText);
    }

    public function telegram(string $text): void
    {
        $token = config('bookstore.telegram.token');
        $chat  = config('bookstore.telegram.chat_id');

        if (! $token || ! $chat) {
            return;
        }

        rescue(fn () => Http::timeout(5)->post("https://api.telegram.org/bot{$token}/sendMessage", [
            'chat_id' => $chat,
            'text' => $text,
            'disable_web_page_preview' => true,
        ]));
    }
}
