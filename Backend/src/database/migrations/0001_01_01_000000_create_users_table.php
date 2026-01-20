<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// TABLA DE USUARIOS

return new class extends Migration
{
    /**
     * Run the migrations
     * Ejecutar la migración
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id_user');
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 255);
            $table->char('phone', 12);
            $table->date('registration_date');
        });
    }

    /**
     * Reverse the migrations
     * Eliminar la migración
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};