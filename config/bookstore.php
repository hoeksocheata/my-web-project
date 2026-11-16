<?php

return [
    // ចំនួនស្តុកដែលចាត់ទុកថា "ជិតអស់"
    'low_stock' => 5,

    // KHQR សម្រាប់ទូទាត់ (ដាក់រូប QR ថ្មីនៅ public/images ហើយប្តូរ path ខាងក្រោម ឬកំណត់ក្នុង .env)
    'khqr' => [
        'image'   => env('KHQR_IMAGE', 'images/khqr.jpg'),
        'account' => env('KHQR_ACCOUNT', 'SOCHEATA HOEK'),
    ],

    // Demo mode: បង្ហាញប៊ូតុង «ចូលជា Admin / អតិថិជន» លើទំព័រ login (បើកតែសម្រាប់ demo!)
    'demo' => (bool) env('BOOKSTORE_DEMO', false),

    // ជូនដំណឹងម្ចាស់ហាងតាម Telegram (ស្រេចចិត្ត) — បង្កើត bot តាម @BotFather ហើយយក chat id
    'telegram' => [
        'token'   => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
    ],
];
