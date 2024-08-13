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
            $table->boolean('heavy_vehicle');
            $table->boolean('wheelchair_adapted');
            $table->integer('capacity');
            $table->decimal('fuel_consuption', 6, 3);
            $table->integer('status_code');
            $table->tinyInteger('current_month_fuel_requests');
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
