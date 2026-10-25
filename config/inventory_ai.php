<?php

return [
    'provider' => env('AI_INVENTORY_PROVIDER', 'mock'),
    'model' => env('AI_INVENTORY_MODEL', ''),
    'url' => env('AI_INVENTORY_URL', 'http://127.0.0.1:11434'),
    'api_key' => env('AI_INVENTORY_API_KEY'),
    'timeout' => (int) env('AI_INVENTORY_TIMEOUT', 30),
];
