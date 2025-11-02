# PWA IP Push Notify - Advanced PWA & Push Notifications

Production-ready PWA and Push Notification system for Laravel with IP-based user tracking.

## Features

✅ **PWA Support**
- Service Worker with offline caching
- Install prompt for mobile/desktop
- Web App Manifest

✅ **Advanced Push Notifications**
- Database-backed subscriptions
- IP-based user tracking (no login required)
- Rich notifications (images, actions, badges)
- Scheduled notifications
- Click tracking
- Statistics & analytics

✅ **Production Ready**
- Database migrations
- Queue support ready
- CLI commands
- Comprehensive API
- Error handling & logging

## Installation

### 1. Install Package

Add to your Laravel project's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "./pwa-ip-push-notify"
        }
    ],
    "require": {
        "pachristo/pwa_ip_push_notify": "*"
    }
}
```

Then run:
```bash
composer require pachristo/pwa_ip_push_notify
```

### 2. Publish Assets & Run Migrations

```bash
php artisan pwa-push:install
php artisan migrate
```

This will publish:
- `/public/pwa-push/` - PWA assets
- `/config/pwa-push.php` - Configuration
- `/database/migrations/` - Database tables

### 3. Add Assets to Layout

Add the following to your layout file (e.g., `resources/views/layouts/app.blade.php`):

```blade
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your App</title>
    
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="/pwa-push/manifest.json">
    <meta name="theme-color" content="#6366f1">
    
    <!-- PWA Styles -->
    <link rel="stylesheet" href="/pwa-push/style.css">
    
    <!-- Your other head content -->
</head>
<body>
    <!-- Your page content -->
    
    <!-- PWA Push Notification Modal (before closing body tag) -->
    <x-pwa-push-modal />
    
    <!-- OR use the view directly -->
    {{-- @include('pwa-push::components.modal') --}}
    
    <!-- IMPORTANT: Scripts stack (required for modal JavaScript) -->
    @stack('scripts')
</body>
</html>
```

**Important Notes:**

- ⚠️ **Required**: `@stack('scripts')` must be present in your layout (before `</body>`)
- The modal uses `@push('scripts')` to inject JavaScript
- Without `@stack('scripts')`, the modal buttons won't work

**Alternative: Manual Setup**

If you want to load assets manually:

```blade
<head>
    <!-- Manifest -->
    <link rel="manifest" href="/pwa-push/manifest.json">
    <meta name="theme-color" content="#6366f1">
    
    <!-- PWA CSS -->
    <link rel="stylesheet" href="/pwa-push/style.css">
</head>

<body>
    <!-- Your content -->
    
    <!-- PWA Modal Component -->
    <x-pwa-push-modal />
    
    <!-- REQUIRED: Scripts stack -->
    @stack('scripts')
    
    <!-- Service Worker is auto-registered by the modal component -->
    
    <!-- OR register service worker manually (if not using modal) -->
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/pwa-push/sw.js')
                .then(reg => console.log('Service Worker registered', reg))
                .catch(err => console.log('Service Worker registration failed', err));
        }
    </script>
</body>
```

**For Alpine.js Users:**

The modal component uses Alpine.js. If you don't have it installed:

```blade
<head>
    <!-- Add Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
```

Or install via npm:

```bash
npm install alpinejs
```

## Configuration

Edit `/config/pwa-push.php` or use environment variables:

```env
PWA_PUSH_NAME="YourApp"
PWA_PUSH_THEME="#216895"
PWA_PUSH_BG="#f0f2f5"
PWA_PUSH_TITLE="New Update"
PWA_PUSH_BODY="You have new notifications"
PWA_PUSH_VAPID_SUBJECT="mailto:admin@yourapp.com"
PWA_PUSH_CLEANUP_DAYS=30
PWA_PUSH_MAX_SUBS_PER_IP=5
```

## Usage

### Send Push Notification via Code

```php
use Pachristo\PwaIpPushNotify\Services\PushService;

$pushService = app(PushService::class);

// Send to specific IP
$result = $pushService->sendToIp('192.168.1.1', [
    'title' => 'Match Starting!',
    'body' => 'Manchester United vs Liverpool - 15:00',
    'icon' => '/images/football.png',
    'image' => '/images/match-preview.jpg',
    'url' => '/matches/123',
    'actions' => [
        ['action' => 'view', 'title' => 'View Match'],
        ['action' => 'close', 'title' => 'Close'],
    ],
]);

// Send to ALL active subscriptions
$result = $pushService->sendToAll([
    'title' => 'Breaking News!',
    'body' => 'New predictions available',
    'url' => '/predictions',
]);
```

### Send via CLI

```bash
# Send to specific IP
php artisan pwa-push:send --title="Match Alert" --body="Starting now" --ip=192.168.1.1

# Send to all users
php artisan pwa-push:send --title="New Tips" --body="Check them out" --all

# With image and URL
php artisan pwa-push:send \
    --title="VIP Tips Ready" \
    --body="3 matches added" \
    --url="/vip-tips" \
    --image="/images/vip.jpg" \
    --all
```

### Send via API

```javascript
// Send custom notification
fetch('/pwa-push/send-custom', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        title: 'New Prediction',
        body: 'Over 2.5 Goals - 85% confidence',
        url: '/predictions/today',
        target: 'all' // or 'ip'
    })
});
```

## API Endpoints

### Public Endpoints

- `GET /pwa-push/vapid` - Get VAPID public key
- `POST /pwa-push/subscribe` - Subscribe to push notifications
- `POST /pwa-push/unsubscribe` - Unsubscribe
- `GET /pwa-push/my-subscriptions` - Get current IP subscriptions
- `GET /pwa-push/send` - Test notification (current IP)

### Admin Endpoints

- `POST /pwa-push/send-custom` - Send custom notification
- `GET /pwa-push/statistics` - Get system statistics

## Database Tables

### `push_subscriptions`
Stores all push subscriptions with IP tracking:
- `ip_address` - User's IP address (indexed)
- `endpoint` - Push subscription endpoint
- `public_key`, `auth_token` - Encryption keys
- `is_active` - Active status
- `notification_count` - Total sent
- `last_notification_at` - Last activity

### `push_notifications`
Stores notification records:
- `title`, `body` - Content
- `icon`, `image`, `badge` - Assets
- `url` - Click target
- `actions` - Action buttons (JSON)
- `status` - pending, sending, sent, failed
- `scheduled_at` - For scheduled sends

### `push_notification_logs`
Tracks delivery and clicks:
- `push_notification_id` - Related notification
- `push_subscription_id` - Related subscription
- `status` - sent, failed, clicked
- `sent_at`, `clicked_at` - Timestamps

## Advanced Features

### Get Statistics

```php
$stats = app(PushService::class)->getStatistics();
/*
[
    'total_subscriptions' => 1500,
    'active_subscriptions' => 1200,
    'unique_ips' => 800,
    'total_notifications_sent' => 5000,
    'total_clicks' => 2500,
]
*/
```

### Cleanup Inactive Subscriptions

```bash
# Auto cleanup (30 days inactive)
php artisan pwa-push:cleanup

# Custom days
php artisan pwa-push:cleanup --days=60
```

### Query Subscriptions

```php
use Pachristo\PwaIpPushNotify\Models\PushSubscription;

// Get all subscriptions for an IP
$subs = PushSubscription::getByIp('192.168.1.1');

// Get all active subscriptions
$active = PushSubscription::getActive();

// Deactivate a subscription
$sub->deactivate();
```

## Example: Football Tips App

```php
// When new predictions are added
public function notifyNewPredictions($match)
{
    $pushService = app(PushService::class);
    
    $pushService->sendToAll([
        'title' => '⚽ New Prediction Available',
        'body' => "{$match->home_team} vs {$match->away_team} - {$match->prediction}",
        'icon' => '/images/ball-icon.png',
        'image' => $match->preview_image,
        'url' => route('predictions.show', $match->id),
        'badge' => '/images/badge.png',
        'actions' => [
            ['action' => 'view', 'title' => '👀 View Details'],
            ['action' => 'share', 'title' => '📤 Share'],
        ],
        'data' => [
            'match_id' => $match->id,
            'odds' => $match->odds,
        ],
    ]);
}

// When match starts (5 minutes before)
public function notifyMatchStarting($match)
{
    $pushService = app(PushService::class);
    
    // Only notify users who viewed this match
    $interestedIPs = $match->views()->pluck('ip_address');
    
    foreach ($interestedIPs as $ip) {
        $pushService->sendToIp($ip, [
            'title' => '🚨 Match Starting Soon!',
            'body' => "{$match->home_team} vs {$match->away_team} in 5 minutes",
            'url' => route('matches.live', $match->id),
            'require_interaction' => true,
            'tag' => "match-{$match->id}", // Replaces previous notifications
        ]);
    }
}
```

## Testing Installation

### 1. Verify Package is Loaded

```bash
php artisan about
# Look for: Pachristo\PwaIpPushNotify\PwaIpPushNotifyServiceProvider
```

### 2. Check Commands

```bash
php artisan list pwa-push
# Should show: pwa-push:install, pwa-push:send, pwa-push:cleanup
```

### 3. Verify Assets

```bash
# Check public files
ls -la public/pwa-push/
# Should show: manifest.json, sw.js, style.css

# Check config
ls -la config/pwa-push.php

# Check migrations ran
php artisan migrate:status | grep push
```

### 4. Test Component Registration

```bash
php artisan tinker
>>> view()->exists('pwa-push::components.modal')
# Should return: true
>>> exit
```

### 5. Test in Browser

1. Visit your app in browser (must be HTTPS or localhost)
2. Click the "Enable Push Notifications" button
3. Allow notifications when prompted
4. Send test notification:

```bash
php artisan pwa-push:send --title="Test" --body="Hello World!" --all
```

## Troubleshooting

### Notifications not working?

1. **Check HTTPS**: Push notifications require secure context (HTTPS or localhost)
2. **Verify migrations**: `php artisan migrate:status | grep push`
3. **Check subscriptions**: `php artisan tinker` → `PushSubscription::count()`
4. **Test VAPID keys**: `ls storage/app/push/vapid.json`
5. **Check browser console**: Open DevTools → Console for errors
6. **Verify service worker**: DevTools → Application → Service Workers

### Component not found error?

```bash
# Clear all caches
php artisan config:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload

# Verify component is registered
php artisan tinker
>>> app('blade.compiler')->getClassComponentAliases()['pwa-push-modal'] ?? 'Not found'
```

### Modal buttons not working?

**Check if `@stack('scripts')` is in your layout:**

```blade
<!-- Your layout file (e.g., app.blade.php) -->
<body>
    <!-- Your content -->
    <x-pwa-push-modal />
    
    <!-- MUST HAVE THIS: -->
    @stack('scripts')
</body>
```

The modal uses `@push('scripts')` to inject JavaScript. Without `@stack('scripts')`, the Install and Allow Push buttons won't work.

### Service Worker not registering?

1. Check browser console for errors
2. Verify file exists: `public/pwa-push/sw.js`
3. Must be served over HTTPS (or localhost)
4. Check scope: Service worker scope is `/`

### Assets not loading?

```bash
# Re-publish assets
php artisan vendor:publish --tag=pwa-push --force

# Verify public directory
ls -la public/pwa-push/
```

### Clear cache if needed

```bash
php artisan config:clear
php artisan view:clear
php artisan cache:clear
php artisan route:clear
composer dump-autoload
```

## License

MIT License - Free to use in your projects!
# PWA-IP-PUSH-NOTIFY
