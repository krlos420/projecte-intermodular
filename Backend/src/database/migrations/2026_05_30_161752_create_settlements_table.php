<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payer_id');
            $table->unsignedInteger('receiver_id');
            $table->unsignedBigInteger('house_id');
            $table->decimal('amount', 8, 2);
            $table->timestamps();

            $table->foreign('payer_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id_user')->on('users')->onDelete('cascade');
            $table->foreign('house_id')->references('id')->on('houses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
