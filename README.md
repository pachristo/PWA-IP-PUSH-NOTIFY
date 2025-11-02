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

### 3. Add Component to Layout

```blade
<!-- In your layout file (e.g., app.blade.php) -->
<x-pwa-push.modal />
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

## Troubleshooting

### Notifications not working?

1. Check HTTPS (required for push)
2. Verify migrations ran: `php artisan migrate:status`
3. Check subscriptions: `php artisan tinker` → `PushSubscription::count()`
4. Test VAPID keys exist: `storage/app/push/vapid.json`

### Clear cache if needed

```bash
php artisan config:clear
php artisan cache:clear
composer dump-autoload
```

## License

MIT License - Free to use in your projects!
# PWA-IP-PUSH-NOTIFY
