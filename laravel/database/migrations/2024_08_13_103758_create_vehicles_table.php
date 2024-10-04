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
            $table->string('license_plate')->unique();
            $table->integer('year');
            $table->boolean('heavy_vehicle');
            $table->enum('heavy_type',['Mercadorias', 'Passageiros'])->nullable();
            $table->boolean('wheelchair_adapted');
            $table->boolean('wheelchair_certified');
            $table->integer('capacity');
            $table->decimal('fuel_consumption', 6, 3);
            $table->enum('status',['Disponível','Indisponível','Em manutenção','Escondido', 'Em Serviço']);
            $table->tinyInteger('current_month_fuel_requests');
            $table->enum('fuel_type',['Gasóleo','Gasolina 95','Gasolina 98','Híbrido','Elétrico']);
            $table->integer('current_kilometrage');
            $table->string('image_path')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index(['heavy_vehicle', 'heavy_type']);
            $table->index('capacity');
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
