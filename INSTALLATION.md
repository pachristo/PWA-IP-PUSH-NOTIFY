# Installation Guide

## Method 1: Local Path Repository (Recommended for Development)

### Step 1: Add to your Laravel app's `composer.json`

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../pwa-ip-push-notify",
            "options": {
                "symlink": true
            }
        }
    ],
    "require": {
        "pachristo/pwa_ip_push_notify": "^1.0"
    }
}
```

### Step 2: Install

```bash
cd /home/pachristo/project/php81/www/tipskings_docker/zepredict_front
composer require pachristo/pwa_ip_push_notify
```

## Method 2: Direct Installation with @dev

If Method 1 doesn't work, try:

```bash
composer require pachristo/pwa_ip_push_notify @dev
```

## Method 3: Allow Dev Stability

Add to your Laravel app's `composer.json`:

```json
{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "repositories": [
        {
            "type": "path",
            "url": "../pwa-ip-push-notify"
        }
    ],
    "require": {
        "pachristo/pwa_ip_push_notify": "*"
    }
}
```

Then run:
```bash
composer update pachristo/pwa_ip_push_notify
```

## After Installation

### 1. Publish Package Assets

```bash
php artisan pwa-push:install
```

This will publish:
- `/public/pwa-push/` - PWA assets (service worker, manifest, icons)
- `/config/pwa-push.php` - Configuration file
- `/database/migrations/` - Database migrations

### 2. Run Migrations

```bash
php artisan migrate
```

This creates 3 tables:
- `push_subscriptions` - User subscriptions with IP tracking
- `push_notifications` - Notification records
- `push_notification_logs` - Delivery tracking

### 3. Add Component to Layout

Add to your main layout file (e.g., `resources/views/layouts/app.blade.php`):

```blade
<!DOCTYPE html>
<html>
<head>
    <!-- Your head content -->
    <link rel="manifest" href="/pwa-push/manifest.json">
</head>
<body>
    <!-- Your content -->
    
    <!-- Add before closing body tag -->
    <x-pwa-push.modal />
</body>
</html>
```

### 4. Configure Environment Variables (Optional)

Add to your `.env`:

```env
PWA_PUSH_NAME="Your App Name"
PWA_PUSH_THEME="#6366f1"
PWA_PUSH_BG="#f0f2f5"
PWA_PUSH_TITLE="New Update"
PWA_PUSH_BODY="You have new notifications"
PWA_PUSH_VAPID_SUBJECT="mailto:admin@yourapp.com"
```

## Testing

### Test Push Notification

```bash
php artisan pwa-push:send --title="Test Notification" --body="Hello World!" --all
```

### View Statistics

```bash
php artisan tinker
>>> $stats = app(\Pachristo\PwaIpPushNotify\Services\PushService::class)->getStatistics();
>>> print_r($stats);
```

## Troubleshooting

### Error: "Could not find package"

**Solution**: Make sure the `url` in repositories points to the correct path:

```bash
# From your Laravel app directory
cd /home/pachristo/project/php81/www/tipskings_docker/zepredict_front
ls ../pwa-ip-push-notify  # Should show the package files
```

If the path is wrong, update `composer.json`:
```json
"repositories": [
    {
        "type": "path",
        "url": "/home/pachristo/project/php81/www/tipskings_docker/pwa-ip-push-notify"
    }
]
```

### Error: "minimum-stability"

**Solution**: Add to your Laravel app's `composer.json`:
```json
{
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

### Error: "Service worker not registering"

**Solution**: Make sure you're using HTTPS or localhost. Push notifications require secure context.

### Clear Composer Cache

If you make changes to the package:
```bash
composer clear-cache
composer dump-autoload
```

## Next Steps

1. ✅ Visit your app in browser
2. ✅ Click "Enable Push Notifications" button
3. ✅ Allow notifications when prompted
4. ✅ Send test notification
5. ✅ Check your browser for the notification!

## Usage Examples

### Send to All Users

```php
use Pachristo\PwaIpPushNotify\Services\PushService;

$pushService = app(PushService::class);
$pushService->sendToAll([
    'title' => '⚽ New Match Prediction',
    'body' => 'Man United vs Arsenal - Over 2.5 Goals',
    'icon' => '/images/football.png',
    'url' => '/predictions/today',
]);
```

### Send to Specific IP

```php
$pushService->sendToIp('192.168.1.100', [
    'title' => 'VIP Tip Available',
    'body' => 'Check your exclusive prediction',
    'url' => '/vip-tips',
]);
```

### CLI Usage

```bash
# Send to all
php artisan pwa-push:send --title="Match Alert" --body="Starting now" --all

# Send to specific IP
php artisan pwa-push:send --title="Personal Alert" --body="Your team is playing" --ip=192.168.1.1

# With image
php artisan pwa-push:send --title="Breaking News" --body="Transfer update" --image="/images/news.jpg" --all
```

---

**Need help?** Check the [README.md](README.md) for full documentation.
