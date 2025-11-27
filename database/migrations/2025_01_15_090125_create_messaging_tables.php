<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagingTables extends Migration
{
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('sender_username');
            $table->text('content');
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();

            $table->index('sender_username');
            $table->index('sent_at');
        });

        Schema::create('message_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');
            $table->string('recipient_username');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('recipient_username');
            $table->index('message_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('message_recipients');
        Schema::dropIfExists('messages');
    }
}
