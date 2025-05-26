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
        Schema::create('entreprises', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary(); // L’id est la clé primaire ici
            $table->string('nom_entreprise');
            $table->string('domaine')->nullable();
            $table->string('logo')->nullable();
            $table->string('IFU');
            $table->string('RCCM')->nullable();
            $table->timestamps();
        
            $table->foreign('id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entreprises');
    }
};
