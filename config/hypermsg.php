<?php

return [
    'api_key' => env('HYPERMSG_API_KEY'),
    'base_url' => env('HYPERMSG_BASE_URL'),
    'whatsapp_number_id' => env('WHATSAPP_NUMBER_ID'),

    // telegram
    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'api_url' => env('TELEGRAM_API_URL', 'https://gatewayapi.telegram.org/'),
    'chat_id' => env('TELEGRAM_CHAT_ID'),

];
