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
        Schema::create('kid_place', function (Blueprint $table) {
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('place_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['kid_id', 'place_id']); // Composite primary key
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kid_place');
    }
};
