<?php
namespace Pachristo\PwaIpPushNotify;
use Illuminate\Support\ServiceProvider;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\VAPID;
use Pachristo\PwaIpPushNotify\Services\PushService;

class PwaIpPushNotifyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'pwa-push');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Register Blade components
        if (method_exists($this->app['blade.compiler'], 'component')) {
            $this->app['blade.compiler']->component(
                \Pachristo\PwaIpPushNotify\Components\Modal::class,
                'pwa-push-modal'
            );
        }

        $this->publishes([
            __DIR__.'/../public'       => public_path('pwa-push'),
            __DIR__.'/../resources/views' => resource_path('views/vendor/pwa-push'),
            __DIR__.'/../config/pwa-push.php' => config_path('pwa-push.php'),
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'pwa-push');

        $this->commands([
            \Pachristo\PwaIpPushNotify\Console\InstallCommand::class,
            \Pachristo\PwaIpPushNotify\Console\SendPushCommand::class,
            \Pachristo\PwaIpPushNotify\Console\CleanupSubscriptionsCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/pwa-push.php', 'pwa-push');

        // Register WebPush singleton
        $this->app->singleton(WebPush::class, fn() => new WebPush(['VAPID' => $this->vapid()]));

        // Register PushService
        $this->app->singleton(PushService::class, function ($app) {
            return new PushService($app->make(WebPush::class));
        });
    }

    private function vapid()
    {
        $path = storage_path('app/push/vapid.json');
        
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        if (!file_exists($path)) {
            $keys = VAPID::createVapidKeys();
            file_put_contents($path, json_encode($keys));
        } else {
            $keys = json_decode(file_get_contents($path), true);
        }

        return [
            'subject' => config('pwa-push.vapid_subject', 'mailto:admin@'.request()->getHost()),
            'publicKey'  => $keys['publicKey'],
            'privateKey' => $keys['privateKey'],
        ];
    }
}
