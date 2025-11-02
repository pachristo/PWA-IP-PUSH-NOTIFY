<?php
return [

    /*
    |--------------------------------------------------------------------------
    | App Name (appears in modal + push)
    |--------------------------------------------------------------------------
    */
    'app_name' => env('PWA_PUSH_NAME', 'PWA Push Notify'),

    /*
    |--------------------------------------------------------------------------
    | Colours (1KB CSS uses these)
    |--------------------------------------------------------------------------
    */
    'theme_color'   => env('PWA_PUSH_THEME', '#6366f1'),   // indigo
    'bg_color'      => env('PWA_PUSH_BG', '#f0f2f5'),     // light gray

    /*
    |--------------------------------------------------------------------------
    | Push message (you can change it live)
    |--------------------------------------------------------------------------
    */
    'push_title' => env('PWA_PUSH_TITLE', 'PWA Push Notify'),
    'push_body'  => env('PWA_PUSH_BODY',  'Push works via your IP!'),

    /*
    |--------------------------------------------------------------------------
    | VAPID Subject (used for push notifications)
    |--------------------------------------------------------------------------
    */
    'vapid_subject' => env('PWA_PUSH_VAPID_SUBJECT', null),

    /*
    |--------------------------------------------------------------------------
    | Subscription Settings
    |--------------------------------------------------------------------------
    */
    'cleanup_inactive_days' => env('PWA_PUSH_CLEANUP_DAYS', 30),
    'max_subscriptions_per_ip' => env('PWA_PUSH_MAX_SUBS_PER_IP', 5),

    /*
    |--------------------------------------------------------------------------
    | Default Notification Settings
    |--------------------------------------------------------------------------
    */
    'default_icon' => env('PWA_PUSH_DEFAULT_ICON', '/pwa-push/icon-192.png'),
    'default_badge' => env('PWA_PUSH_DEFAULT_BADGE', '/pwa-push/icon-192.png'),

];
