<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWishlistsTable extends Migration
{
    public function up()
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->unsignedBigInteger('album_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('set null');
            $table->unique(['user_id', 'media_id']);
            $table->unique(['user_id', 'album_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('wishlists');
    }
}