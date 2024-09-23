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
        Schema::create('order_stops', function (Blueprint $table) {             //TODO: ADD MORE FOREIGN AS MORE TABLES ARE ADDED (ORDER_STATUS)
            $table->id();                                                       //TODO: CHECK CASCADES ON EVERY TABLE
            $table->timestamps();
            $table->string('address');
            $table->geography('coordinates', subtype: 'point', srid: 4326);

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_stops');
    }
};
