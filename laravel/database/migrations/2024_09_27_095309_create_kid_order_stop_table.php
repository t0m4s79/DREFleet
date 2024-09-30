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
        Schema::create('kid_order_stop', function (Blueprint $table) {
            $table->foreignId('order_stop_id')->constrained()->onDelete('cascade');
            $table->foreignId('kid_id')->constrained()->onDelete('cascade');
            $table->foreignId('place_id')->constrained()->onDelete('cascade');

            $table->primary(['kid_id', 'place_id', 'order_stop_id']); // Composite primary key            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kid_order_stop');
    }
};
