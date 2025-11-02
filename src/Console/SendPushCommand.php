<?php

namespace Pachristo\PwaIpPushNotify\Console;

use Illuminate\Console\Command;
use Pachristo\PwaIpPushNotify\Services\PushService;

class SendPushCommand extends Command
{
    protected $signature = 'pwa-push:send 
                            {--title= : Notification title}
                            {--body= : Notification body}
                            {--url= : URL to open when clicked}
                            {--image= : Image URL}
                            {--ip= : Target specific IP address}
                            {--all : Send to all active subscriptions}';

    protected $description = 'Send push notification via command line';

    public function handle(PushService $pushService)
    {
        $title = $this->option('title') ?: config('pwa-push.app_name');
        $body = $this->option('body') ?: config('pwa-push.push_body');
        $url = $this->option('url');
        $image = $this->option('image');
        $ip = $this->option('ip');
        $sendToAll = $this->option('all');

        $notificationData = [
            'title' => $title,
            'body' => $body,
            'icon' => '/pwa-push/icon-192.png',
        ];

        if ($url) {
            $notificationData['url'] = $url;
        }

        if ($image) {
            $notificationData['image'] = $image;
        }

        $this->info('Preparing to send push notification...');
        $this->line("Title: {$title}");
        $this->line("Body: {$body}");

        if ($sendToAll) {
            $this->info('Sending to ALL active subscriptions...');
            $result = $pushService->sendToAll($notificationData);
        } elseif ($ip) {
            $this->info("Sending to IP: {$ip}");
            $result = $pushService->sendToIp($ip, $notificationData);
        } else {
            $this->error('Please specify --ip=<address> or --all');
            return 1;
        }

        if ($result['success']) {
            $this->info("✓ Successfully sent to {$result['sent']} device(s)");
            
            if ($result['failed'] > 0) {
                $this->warn("✗ Failed to send to {$result['failed']} device(s)");
            }

            return 0;
        }

        $this->error("✗ Failed: {$result['message']}");
        return 1;
    }
}
