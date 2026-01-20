<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TABLA MENSAJES

return new class extends Migration
{
    /**
     * Run the migrations
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id_message');
            $table->integer('id_product');
            $table->integer('id_transmitter');
            $table->integer('id_receiver');
            $table->text('content');
            $table->date('shipping_date');
        });
    }

    /**
     * Reverse the migrations
     * Eliminar la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
