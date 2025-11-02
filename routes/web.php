<?php
use Illuminate\Support\Facades\Route;
use Pachristo\PwaIpPushNotify\Http\Controllers\PushController;

Route::prefix('pwa-push')->name('pwa-push.')->group(function () {
    // Public endpoints
    Route::get('vapid', [PushController::class, 'vapid'])->name('vapid');
    Route::post('subscribe', [PushController::class, 'subscribe'])->name('subscribe');
    Route::post('unsubscribe', [PushController::class, 'unsubscribe'])->name('unsubscribe');
    Route::get('my-subscriptions', [PushController::class, 'mySubscriptions'])->name('my-subscriptions');
    
    // Test notification (for current IP)
    Route::get('send', [PushController::class, 'send'])->name('send');
    
    // Admin endpoints (you may want to add middleware)
    Route::post('send-custom', [PushController::class, 'sendCustom'])->name('send-custom');
    Route::get('statistics', [PushController::class, 'statistics'])->name('statistics');
});
