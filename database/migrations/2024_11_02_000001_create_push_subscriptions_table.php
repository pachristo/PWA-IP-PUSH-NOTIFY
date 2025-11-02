<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45)->index();
            $table->string('user_agent')->nullable();
            $table->string('endpoint', 500)->unique();
            $table->text('public_key');
            $table->text('auth_token');
            $table->text('content_encoding')->nullable();
            $table->string('country_code', 10)->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_notification_at')->nullable();
            $table->integer('notification_count')->default(0);
            $table->timestamps();
            
            $table->index(['ip_address', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_subscriptions');
    }
};
