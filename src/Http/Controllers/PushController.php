<?php

namespace Pachristo\PwaIpPushNotify\Http\Controllers;

use Illuminate\Http\Request;
use Minishlink\WebPush\WebPush;
use Pachristo\PwaIpPushNotify\Services\PushService;
use Pachristo\PwaIpPushNotify\Models\PushSubscription;

class PushController
{
    protected PushService $pushService;

    public function __construct(PushService $pushService)
    {
        $this->pushService = $pushService;
    }

    /**
     * Get VAPID public key
     */
    public function vapid()
    {
        return ['key' => app(WebPush::class)->getVapidPublicKey()];
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
        ]);

        try {
            $subscription = $this->pushService->subscribe(
                $validated,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscribed successfully',
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test notification to current IP
     */
    public function send(Request $request)
    {
        $result = $this->pushService->sendToIp($request->ip(), [
            'title' => config('pwa-push.app_name'),
            'body' => config('pwa-push.push_body'),
            'icon' => '/pwa-push/icon-192.png',
            'url' => url('/'),
        ]);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Notification sent to ' . $result['sent'] . ' device(s)',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 404);
    }

    /**
     * Send custom notification
     */
    public function sendCustom(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|string',
            'url' => 'nullable|url',
            'actions' => 'nullable|array',
            'target' => 'nullable|in:all,ip',
            'ip_address' => 'required_if:target,ip|ip',
        ]);

        $target = $validated['target'] ?? 'ip';
        unset($validated['target'], $validated['ip_address']);

        if ($target === 'all') {
            $result = $this->pushService->sendToAll($validated);
        } else {
            $result = $this->pushService->sendToIp(
                $request->input('ip_address', $request->ip()),
                $validated
            );
        }

        return response()->json($result);
    }

    /**
     * Get statistics
     */
    public function statistics()
    {
        return response()->json([
            'success' => true,
            'data' => $this->pushService->getStatistics(),
        ]);
    }

    /**
     * Get subscriptions for current IP
     */
    public function mySubscriptions(Request $request)
    {
        $subscriptions = PushSubscription::where('ip_address', $request->ip())
            ->where('is_active', true)
            ->get(['id', 'created_at', 'last_notification_at', 'notification_count']);

        return response()->json([
            'success' => true,
            'count' => $subscriptions->count(),
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Unsubscribe
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $subscription = PushSubscription::where('endpoint', $validated['endpoint'])->first();

        if ($subscription) {
            $subscription->deactivate();
            
            return response()->json([
                'success' => true,
                'message' => 'Unsubscribed successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Subscription not found',
        ], 404);
    }
}