<?php

return [
    'exchange_rate' => (int) env('GEMS_EXCHANGE_RATE', 1000),
    'cashback_percent' => (int) env('GEMS_CASHBACK_PERCENT', 5),
    'min_topup_vnd' => (int) env('GEMS_MIN_TOPUP_VND', 50000),
    'max_topup_vnd' => (int) env('GEMS_MAX_TOPUP_VND', 5000000),
    'sepay' => [
        'account_number' => env('SEPAY_ACCOUNT_NUMBER'),
        'bank_code' => env('SEPAY_BANK_CODE'),
        'api_key' => env('SEPAY_API_KEY'),
        'allowed_ips' => env('SEPAY_ALLOWED_IPS', '14.225.204.68,103.163.218.2,103.163.218.66,103.163.218.146,103.163.218.147,14.225.204.130'),
    ],
];
