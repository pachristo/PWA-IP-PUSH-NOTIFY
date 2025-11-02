<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('push_notification_id')->constrained()->onDelete('cascade');
            $table->foreignId('push_subscription_id')->constrained()->onDelete('cascade');
            $table->string('status'); // sent, failed, clicked
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();
            
            $table->index(['push_notification_id', 'status']);
            $table->index(['push_subscription_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_notification_logs');
    }
};
