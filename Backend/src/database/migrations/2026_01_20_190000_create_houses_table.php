<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para crear la tabla 'houses'.
 * Esta tabla representa los pisos/casas compartidas en la aplicación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('houses', function (Blueprint $table) {
            // Clave primaria autoincremental
            $table->id();
            
            // Nombre del piso (ej: "Piso Calle Mayor 5")
            $table->string('name');
            
            // Código único para invitar a otros usuarios a unirse al piso
            // Es unique porque cada piso debe tener un código diferente
            $table->string('invite_code')->unique();
            
            // Timestamps: created_at y updated_at automáticos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('houses');
    }
};
