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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->unique();
            $table->enum('user_type',['Nenhum','Condutor','Gestor','Técnico','Administrador'])->default('Nenhum');
            $table->enum('status', ['Disponível', 'Indisponível', 'Em Serviço', 'Escondido']);

            $table->index('user_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
