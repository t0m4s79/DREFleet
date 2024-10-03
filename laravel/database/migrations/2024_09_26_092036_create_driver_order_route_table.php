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
        Schema::create('driver_order_route', function (Blueprint $table) {
            $table->unsignedBigInteger('order_route_id');
            $table->foreign('order_route_id')->references('id')->on('order_routes');
            
            $table->unsignedBigInteger('driver_user_id');
            $table->foreign('driver_user_id')->references('user_id')->on('drivers');

            $table->timestamps();
        
            $table->primary(['order_route_id', 'driver_user_id']); // Composite primary key 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_order_route');
    }
};
