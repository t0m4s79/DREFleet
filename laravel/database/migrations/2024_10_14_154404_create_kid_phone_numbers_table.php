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
        Schema::create('kid_phone_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone');
            $table->string('owner_name');
            $table->string('relationship_to_kid');
            $table->enum('preference', ['Preferido', 'Alternativo']);       // To specify which contact is the prefered/primary one

            $table->unsignedBigInteger('kid_id');
            $table->foreign('kid_id')->references('id')->on('kids')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kid_phone_numbers');
    }
};
