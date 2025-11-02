<?php

namespace Pachristo\PwaIpPushNotify\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PushNotification extends Model
{
    protected $fillable = [
        'title',
        'body',
        'icon',
        'image',
        'badge',
        'url',
        'actions',
        'data',
        'tag',
        'require_interaction',
        'sent_count',
        'clicked_count',
        'scheduled_at',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'actions' => 'array',
        'data' => 'array',
        'require_interaction' => 'boolean',
        'sent_count' => 'integer',
        'clicked_count' => 'integer',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    /**
     * Get logs for this notification
     */
    public function logs(): HasMany
    {
        return $this->hasMany(PushNotificationLog::class);
    }

    /**
     * Get subscriptions that received this notification
     */
    public function subscriptions(): BelongsToMany
    {
        return $this->belongsToMany(PushSubscription::class, 'push_notification_logs')
            ->withPivot(['status', 'sent_at', 'clicked_at'])
            ->withTimestamps();
    }

    /**
     * Get pending notifications
     */
    public static function getPending()
    {
        return static::where('status', 'pending')
            ->where(function ($query) {
                $query->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', now());
            })
            ->get();
    }

    /**
     * Mark as sent
     */
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark as failed
     */
    public function markAsFailed()
    {
        $this->update(['status' => 'failed']);
    }

    /**
     * Increment sent count
     */
    public function incrementSentCount()
    {
        $this->increment('sent_count');
    }

    /**
     * Increment clicked count
     */
    public function incrementClickedCount()
    {
        $this->increment('clicked_count');
    }

    /**
     * Get notification payload for WebPush
     */
    public function getPayload(): array
    {
        $payload = [
            'title' => $this->title,
            'body' => $this->body,
            'icon' => $this->icon ?? '/pwa-push/icon-192.png',
        ];

        if ($this->image) {
            $payload['image'] = $this->image;
        }

        if ($this->badge) {
            $payload['badge'] = $this->badge;
        }

        if ($this->url) {
            $payload['data'] = array_merge($this->data ?? [], ['url' => $this->url]);
        } elseif ($this->data) {
            $payload['data'] = $this->data;
        }

        if ($this->actions) {
            $payload['actions'] = $this->actions;
        }

        if ($this->tag) {
            $payload['tag'] = $this->tag;
        }

        if ($this->require_interaction) {
            $payload['requireInteraction'] = true;
        }

        return $payload;
    }
}
