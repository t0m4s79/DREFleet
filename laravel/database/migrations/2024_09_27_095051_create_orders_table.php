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
        //TODO: ADD MORE FOREIGN AS MORE TABLES ARE ADDED (ORDER_STATUS)
        //TODO: IF A USER IS DELETED WHAT HAPPENS TO A ORDER -> STATUS FOR EVERY TABLE SHOULD HOLD A DELETED OPTION INSTEAD OF REMOVING FROM DB
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('begin_address');
            $table->string('end_address');
            $table->dateTime('planned_begin_date');
            $table->dateTime('actual_begin_date')->nullable();
            $table->dateTime('planned_end_date');
            $table->dateTime('actual_end_date')->nullable();
            $table->geography('begin_coordinates', subtype: 'point', srid: 4326);
            $table->geography('end_coordinates', subtype: 'point', srid: 4326);
            $table->json('trajectory');
            $table->dateTime('approved_date')->nullable();
            $table->enum('order_type', ['Transporte de Pessoal','Transporte de Mercadorias','Transporte de Crianças', 'Outros']);

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->unsignedBigInteger('driver_id');
            $table->foreign('driver_id')->references('user_id')->on('drivers')->onDelete('cascade');

            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('technician_id');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('cascade');

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
