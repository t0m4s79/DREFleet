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
        Schema::create('order_stops', function (Blueprint $table) {
            $table->id();
            $table->integer('stop_number');
            $table->dateTime('expected_arrival_date')->nullable();
            $table->dateTime('actual_arrival_date')->nullable();
            $table->decimal('time_from_previous_stop');
            $table->decimal('distance_from_previous_stop');

            $table->unsignedBigInteger('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');

            $table->unsignedBigInteger('place_id');
            $table->foreign('place_id')->references('id')->on('places')->onDelete('cascade');

            $table->timestamps();

            $table->primary(['id', 'place_id']);

            $table->index('order_id');
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
