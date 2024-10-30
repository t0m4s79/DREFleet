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
        Schema::create('vehicle_maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->date('begin_date');
            $table->date('end_date')->nullable();
            $table->enum('type', ['Manutenção', 'Anomalia', 'Reparação', 'Outros']);
            $table->string('description');
            $table->integer('kilometrage')->nullable();
            $table->decimal('total_cost', 8, 2)->nullable();
            $table->json('items_cost')->nullable();
            $table->string('service_provider')->nullable();
            $table->enum('status', ['Agendado', 'A decorrer', 'Finalizado']);

            $table->unsignedBigInteger('vehicle_id');
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_reports');
    }
};
