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
        Schema::create('order_route_user', function (Blueprint $table) {
            $table->unsignedBigInteger('order_route_id');
            $table->foreign('order_route_id')->references('id')->on('order_routes');

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
        
            $table->primary(['order_route_id', 'user_id']); // Composite primary key 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_route_user');
    }
};
