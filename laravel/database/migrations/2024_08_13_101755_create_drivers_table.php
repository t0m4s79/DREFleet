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
        Schema::create('drivers', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->primary();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string("license_number")->unique();
            $table->boolean('heavy_license');
            $table->enum('heavy_license_type',['Mercadorias', 'Passageiros'])->nullable();
            $table->date('license_expiration_date');
            $table->timestamps();

            $table->index('license_number');
            $table->index(['heavy_license','heavy_license_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
