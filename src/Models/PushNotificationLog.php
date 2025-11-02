<?php

namespace Pachristo\PwaIpPushNotify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushNotificationLog extends Model
{
    protected $fillable = [
        'push_notification_id',
        'push_subscription_id',
        'status',
        'error_message',
        'sent_at',
        'clicked_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the notification
     */
    public function notification(): BelongsTo
    {
        return $this->belongsTo(PushNotification::class, 'push_notification_id');
    }

    /**
     * Get the subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PushSubscription::class, 'push_subscription_id');
    }

    /**
     * Create a sent log
     */
    public static function logSent($notificationId, $subscriptionId)
    {
        return static::create([
            'push_notification_id' => $notificationId,
            'push_subscription_id' => $subscriptionId,
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Create a failed log
     */
    public static function logFailed($notificationId, $subscriptionId, $error)
    {
        return static::create([
            'push_notification_id' => $notificationId,
            'push_subscription_id' => $subscriptionId,
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    /**
     * Mark as clicked
     */
    public function markAsClicked()
    {
        $this->update([
            'status' => 'clicked',
            'clicked_at' => now(),
        ]);
    }
}
