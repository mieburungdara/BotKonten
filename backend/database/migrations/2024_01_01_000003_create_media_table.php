<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id')->unique();
            $table->unsignedBigInteger('bot_id');
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->string('file_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('caption')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('category')->nullable();
            $table->unsignedBigInteger('backup_message_id')->nullable();
            $table->unsignedBigInteger('backup_channel_id')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->foreign('bot_id')->references('id')->on('bots')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['bot_id', 'is_published']);
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}