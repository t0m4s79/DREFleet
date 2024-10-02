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
        //TODO: ADD MORE FOREIGN AS MORE TABLES ARE ADDED (STATUS)
        //TODO: IF A USER IS DELETED WHAT HAPPENS TO A ORDER -> STATUS FOR EVERY TABLE SHOULD HOLD A DELETED OPTION INSTEAD OF REMOVING FROM DB
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->json('trajectory');
            $table->dateTime('expected_begin_date')->nullable();
            $table->dateTime('expected_end_date')->nullable();
            $table->dateTime('approved_date')->nullable();
            $table->enum('order_type', ['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']);
            $table->enum('status', ['Por aprovar', 'Cancelado/Não aprovado', 'Aprovado', 'Em curso', 'Finalizado', 'Interrompido']);

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');

            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('user_id')->on('drivers')->onDelete('set null');

            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('technician_id')->nullable();
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('order_route_id')->nullable();
            $table->foreign('order_route_id')->references('id')->on('order_routes')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
