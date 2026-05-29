<?php

return [
    'api_key' => env('HYPERMSG_API_KEY'),
    'base_url' => env('HYPERMSG_BASE_URL'),
    'whatsapp_number_id' => env('WHATSAPP_NUMBER_ID'),

    // msgPlus SMS
    'msgplus_api_key' => env('MSGPLUS_API_KEY'),
    'msgplus_base_url' => env('MSGPLUS_BASE_URL', 'https://sms.msgplus.tech/api/v1'),
    'msgplus_sender_id' => env('MSGPLUS_SENDER_ID', 1),
    'msgplus_template_id' => env('MSGPLUS_TEMPLATE_ID', 1),
    'otp_channel' => env('OTP_CHANNEL', 'telegram'),

    // telegram
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'api_url' => env('TELEGRAM_API_URL', 'https://gatewayapi.telegram.org/'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),

    'reservation' => [
        'deposit_percentage' => env('RESERVATION_DEPOSIT_PERCENTAGE', 0.1),
    ],
];
