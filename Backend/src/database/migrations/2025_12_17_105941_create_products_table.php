<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TABLA PRODUCTOS

return new class extends Migration
{
    /**
     * Run the migrations
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id_product');
            $table->integer('id_user');
            $table->integer('id_delivery_point');
            $table->string('name', 255);
            $table->text('description');
            $table->float('price');
            $table->float('stock');
            $table->string('image')->nullable();
            $table->enum('type_stock', ['Kg', 'Unidad']);
            $table->enum('state', ['Agotado', 'Reservado', 'Disponible']);
            $table->date('publication_date');
        });
    }

    /**
     * Reverse the migrations
     * Eliminar la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
