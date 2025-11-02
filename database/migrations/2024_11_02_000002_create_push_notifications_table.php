<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->string('badge')->nullable();
            $table->string('url')->nullable();
            $table->json('actions')->nullable();
            $table->json('data')->nullable();
            $table->string('tag')->nullable();
            $table->boolean('require_interaction')->default(false);
            $table->integer('sent_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('pending'); // pending, sending, sent, failed
            $table->timestamps();
            
            $table->index(['status', 'scheduled_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('push_notifications');
    }
};
