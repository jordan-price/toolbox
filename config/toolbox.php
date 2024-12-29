<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tool Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the various tools available in the toolbox
    |
    */

    'weather' => [
        'api_key' => env('WEATHER_API_KEY'),
        'default_units' => 'metric', // metric or imperial
    ],

    'email' => [
        'from_address' => env('MAIL_FROM_ADDRESS'),
        'from_name' => env('MAIL_FROM_NAME'),
    ],

    'crypto' => [
        'api_key' => env('CRYPTO_API_KEY'),
        'default_currency' => 'usd',
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the AI model and behavior
    |
    */
    'ai' => [
        'model' => env('TOOLBOX_AI_MODEL', 'gpt-4'),
        'system_prompt' => env('TOOLBOX_SYSTEM_PROMPT', 'You are a helpful AI assistant that can help with calculations, weather information, emails, time-related queries, Eloquent queries, and cryptocurrency prices.

IMPORTANT: When handling requests that require multiple pieces of information:
1. You MUST call each required tool separately and in sequence
2. For weather information, ALWAYS use the weather tool with the location
3. For cryptocurrency prices, ALWAYS use the crypto tool with the coin name
4. For time information, ALWAYS use the time tool with the timezone
5. Only after collecting ALL required information, use the email tool to send the combined results

Never skip any required tool calls. Always get fresh data from each tool.'),
    ],
];
