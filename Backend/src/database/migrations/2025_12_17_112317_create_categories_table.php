<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TABLA CATEGORIAS

return new class extends Migration
{
    /**
     * Run the migrations
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->increments('id_category');
            $table->string('name', 255);
        });
    }

    /**
     * Reverse the migrations
     * Eliminar la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
