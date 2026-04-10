<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharesTable extends Migration
{
    public function up()
    {
        Schema::create('shares', function (Blueprint $table) {
            $table->id();
            $table->string('shareable_type'); // 'media' or 'album'
            $table->unsignedBigInteger('shareable_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('code', 12)->unique();
            $table->integer('click_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['shareable_type', 'shareable_id']);
            $table->index('code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shares');
    }
}