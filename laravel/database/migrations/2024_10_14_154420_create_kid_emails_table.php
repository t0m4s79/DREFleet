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
        Schema::create('kid_emails', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string('relationship_to_kid');
            $table->string('email');
            $table->enum('preference', ['Preferida', 'Alternativa']);       // To specify which contact is the prefered/primary one

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
        Schema::dropIfExists('kid_emails');
    }
};
