<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('username');
            $table->timestamp('last_read_at')->nullable();
            $table->timestamps();

            $table->foreign('conversation_id')->references('id')->on('conversations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};
