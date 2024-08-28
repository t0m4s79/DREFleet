<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('make');
            $table->string('model');
            $table->string('license_plate');
            $table->Integer('year');
            $table->boolean('heavy_vehicle');
            $table->boolean('wheelchair_adapted');
            $table->integer('capacity');
            $table->decimal('fuel_consumption', 6, 3);
            $table->enum('status',['Disponível','Indisponível','Em manutenção','Escondido']);
            $table->tinyInteger('current_month_fuel_requests');
            $table->enum('oil_type',['Gasóleo','Gasolina 95','Gasolina 98','Híbrido','Elétrico']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
