<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('media_id')->nullable();
            $table->unsignedBigInteger('album_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('simulation');
            $table->string('payment_status')->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('media_id')->references('id')->on('media')->onDelete('set null');
            $table->foreign('album_id')->references('id')->on('albums')->onDelete('set null');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}