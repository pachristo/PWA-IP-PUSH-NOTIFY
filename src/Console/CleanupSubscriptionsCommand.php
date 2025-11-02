<?php

namespace Pachristo\PwaIpPushNotify\Console;

use Illuminate\Console\Command;
use Pachristo\PwaIpPushNotify\Services\PushService;

class CleanupSubscriptionsCommand extends Command
{
    protected $signature = 'pwa-push:cleanup {--days=30 : Days of inactivity before cleanup}';
    
    protected $description = 'Clean up inactive push subscriptions';

    public function handle(PushService $pushService)
    {
        $days = (int) $this->option('days');

        $this->info("Cleaning up subscriptions inactive for {$days} days...");

        $deleted = $pushService->cleanupInactiveSubscriptions($days);

        $this->info("✓ Cleaned up {$deleted} inactive subscription(s)");

        return 0;
    }
}
