<?php
namespace Pachristo\PwaIpPushNotify\Console;
use Illuminate\Console\Command;
class InstallCommand extends Command
{
    protected $signature = 'pwa-push:install';
    protected $description = 'Install PWA IP Push Notify';
    public function handle()
    {
        $this->call('vendor:publish', ['--tag'=>'pwa-push']);
        $this->info('PWA IP Push Notify ready! Use: <x-pwa-push.modal />');
    }
}
