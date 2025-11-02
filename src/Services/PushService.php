<?php

namespace Pachristo\PwaIpPushNotify\Services;

use Pachristo\PwaIpPushNotify\Models\PushSubscription;
use Pachristo\PwaIpPushNotify\Models\PushNotification;
use Pachristo\PwaIpPushNotify\Models\PushNotificationLog;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushService
{
    protected WebPush $webPush;

    public function __construct(WebPush $webPush)
    {
        $this->webPush = $webPush;
    }

    /**
     * Subscribe a new endpoint
     */
    public function subscribe(array $subscriptionData, string $ipAddress, ?string $userAgent = null): PushSubscription
    {
        // Check if endpoint already exists
        $existing = PushSubscription::where('endpoint', $subscriptionData['endpoint'])->first();
        
        if ($existing) {
            // Update existing subscription
            $existing->update([
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'is_active' => true,
            ]);
            return $existing;
        }

        // Create new subscription
        return PushSubscription::create([
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'endpoint' => $subscriptionData['endpoint'],
            'public_key' => $subscriptionData['keys']['p256dh'] ?? '',
            'auth_token' => $subscriptionData['keys']['auth'] ?? '',
            'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aes128gcm',
        ]);
    }

    /**
     * Create a new notification
     */
    public function createNotification(array $data): PushNotification
    {
        return PushNotification::create($data);
    }

    /**
     * Send notification to specific IP address
     */
    public function sendToIp(string $ipAddress, array $notificationData): array
    {
        $subscriptions = PushSubscription::getByIp($ipAddress);
        
        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'No subscriptions found for this IP'];
        }

        $notification = $this->createNotification($notificationData);
        
        return $this->sendNotification($notification, $subscriptions);
    }

    /**
     * Send notification to all active subscriptions
     */
    public function sendToAll(array $notificationData): array
    {
        $subscriptions = PushSubscription::getActive();
        
        if ($subscriptions->isEmpty()) {
            return ['success' => false, 'message' => 'No active subscriptions found'];
        }

        $notification = $this->createNotification($notificationData);
        
        return $this->sendNotification($notification, $subscriptions);
    }

    /**
     * Send notification to specific subscriptions
     */
    public function sendNotification(PushNotification $notification, $subscriptions): array
    {
        $notification->update(['status' => 'sending']);
        
        $successCount = 0;
        $failureCount = 0;
        $errors = [];

        foreach ($subscriptions as $subscription) {
            try {
                $webPushSubscription = Subscription::create($subscription->toSubscriptionArray());
                
                $this->webPush->queueNotification(
                    $webPushSubscription,
                    json_encode($notification->getPayload())
                );

                $subscription->incrementNotificationCount();
                $successCount++;

            } catch (\Exception $e) {
                $failureCount++;
                $errors[] = [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ];
                
                PushNotificationLog::logFailed($notification->id, $subscription->id, $e->getMessage());
            }
        }

        // Flush the queue
        $results = $this->webPush->flush();

        // Process results
        foreach ($results as $result) {
            $subscription = $subscriptions->firstWhere('endpoint', $result['endpoint']);
            
            if ($result['success']) {
                PushNotificationLog::logSent($notification->id, $subscription->id);
                $notification->incrementSentCount();
            } else {
                $subscription->deactivate();
                PushNotificationLog::logFailed(
                    $notification->id, 
                    $subscription->id, 
                    $result['reason'] ?? 'Unknown error'
                );
            }
        }

        $notification->markAsSent();

        return [
            'success' => true,
            'notification_id' => $notification->id,
            'sent' => $successCount,
            'failed' => $failureCount,
            'errors' => $errors,
        ];
    }

    /**
     * Process pending scheduled notifications
     */
    public function processPendingNotifications(): void
    {
        $pending = PushNotification::getPending();

        foreach ($pending as $notification) {
            $subscriptions = PushSubscription::getActive();
            $this->sendNotification($notification, $subscriptions);
        }
    }

    /**
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_subscriptions' => PushSubscription::count(),
            'active_subscriptions' => PushSubscription::where('is_active', true)->count(),
            'unique_ips' => PushSubscription::distinct('ip_address')->count(),
            'total_notifications_sent' => PushNotification::where('status', 'sent')->count(),
            'total_notifications_pending' => PushNotification::where('status', 'pending')->count(),
            'total_clicks' => PushNotificationLog::where('status', 'clicked')->count(),
        ];
    }

    /**
     * Clean up inactive subscriptions
     */
    public function cleanupInactiveSubscriptions(int $daysInactive = 30): int
    {
        $date = now()->subDays($daysInactive);
        
        return PushSubscription::where('last_notification_at', '<', $date)
            ->orWhereNull('last_notification_at')
            ->where('created_at', '<', $date)
            ->delete();
    }
}
