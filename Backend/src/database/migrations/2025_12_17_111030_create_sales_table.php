<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TABLA VENTAS

return new class extends Migration
{
    /**
     * Run the migrations
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->increments('id_sale');
            $table->integer('id_product');
            $table->integer('id_buyer');
            $table->integer('id_seller');
            $table->integer('id_delivery_point');
            $table->date('sale_date');
            $table->double('total');
            $table->date('collection_date');
            $table->enum('state', ['Pendiente', 'En Curso', 'Terminado']);
        });
    }

    /**
     * Reverse the migrations
     * Eliminar la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
