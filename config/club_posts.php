<?php

return [
    'disk' => env('CLUB_POSTS_DISK', 'public'),

    'content' => [
        'max_length' => 5000,
        'allowed_tags' => '<p><br><strong><em><s><a><ul><ol><li>',
    ],

    'images' => [
        'max_count' => 10,
        'max_size' => 5 * 1024, // 5MB in KB
        'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
    ],

    'videos' => [
        'max_count' => 1,
        'max_size' => 50 * 1024, // 50MB in KB
        'allowed_mimes' => ['mp4', 'mov', 'webm'],
    ],

    'feed' => [
        'per_page' => 10,
    ],
];
