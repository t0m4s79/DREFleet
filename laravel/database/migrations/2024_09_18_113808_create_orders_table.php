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
        Schema::create('orders', function (Blueprint $table) {      //TODO: ADD MORE FOREIGN AS MORE TABLES ARE ADDED (ORDER_STATUS)
            $table->id();                                           //TODO: I A USER IS DELETED WHAT HAPPENS TO A ORDER -> STATUS FOR EVERY TABLE SHOULD HOLD A DELETED OPTION INSTEAD OF REMOVING FROM DB
            $table->timestamps();
            $table->string('begin_address');
            $table->string('end_address');
            $table->dateTime('begin_date');
            $table->dateTime('end_date');
            $table->geography('begin_coordinates', subtype: 'point', srid: 4326);
            $table->geography('end_coordinates', subtype: 'point', srid: 4326);;
            $table->json('trajectory');
            $table->dateTime('approved_date');

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->unsignedBigInteger('driver_id');
            $table->foreign('driver_id')->references('user_id')->on('drivers')->onDelete('cascade');

            $table->unsignedBigInteger('manager_id');
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('cascade');

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
