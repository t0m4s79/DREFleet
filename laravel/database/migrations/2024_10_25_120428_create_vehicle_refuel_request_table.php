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
        Schema::create('vehicle_refuel_requests', function (Blueprint $table) {
            $table->id();

            $table->date('date');
            $table->decimal('quantity', 8, 3);
            $table->decimal('cost_per_unit', 6, 3);
            $table->decimal('total_cost', 8, 2);
            $table->integer('kilometrage');
            $table->enum('fuel_type', ['Gasóleo','Gasolina 95','Gasolina 98','Elétrico']);
            $table->enum('request_type', ['Normal', 'Especial', 'Excepcional']);
            $table->integer('monthly_request_number');

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_refuel_requests');
    }
};
