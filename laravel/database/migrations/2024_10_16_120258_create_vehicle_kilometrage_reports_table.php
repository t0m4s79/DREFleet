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
        Schema::create('vehicle_kilometrage_reports', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->integer('begin_kilometrage');
            $table->integer('end_kilometrage');

            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('set null');

            $table->unsignedBigInteger('driver_id')->nullable();
            $table->foreign('driver_id')->references('user_id')->on('drivers')->onDelete('set null');

            $table->timestamps();

            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_kilometrage_reports');
    }
};
