<?php

namespace Pachristo\PwaIpPushNotify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PushSubscription extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
        'country_code',
        'city',
        'is_active',
        'last_notification_at',
        'notification_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_notification_at' => 'datetime',
        'notification_count' => 'integer',
    ];

    /**
     * Get logs for this subscription
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PushNotificationLog::class);
    }

    /**
     * Get active subscriptions by IP
     */
    public static function getByIp(string $ip)
    {
        return static::where('ip_address', $ip)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get all active subscriptions
     */
    public static function getActive()
    {
        return static::where('is_active', true)->get();
    }

    /**
     * Mark as inactive
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Increment notification count
     */
    public function incrementNotificationCount()
    {
        $this->increment('notification_count');
        $this->update(['last_notification_at' => now()]);
    }

    /**
     * Get subscription array for WebPush library
     */
    public function toSubscriptionArray(): array
    {
        return [
            'endpoint' => $this->endpoint,
            'keys' => [
                'p256dh' => $this->public_key,
                'auth' => $this->auth_token,
            ],
            'contentEncoding' => $this->content_encoding ?? 'aes128gcm',
        ];
    }
}
