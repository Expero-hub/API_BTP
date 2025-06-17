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
        Schema::create('candidature_stages', function (Blueprint $table) {
            $table->id();
            $table->string('cv');
            $table->string('cip');
            $table->string('diplome')->nullable();
             $table->string('lettre_motivation')->nullable();
              $table->enum('statut', ['en_attente', 'acceptee', 'rejettee'])->default('en_attente');
            $table->foreignId('offre_stage_id')->constrained('offre_stages')->onDelete('cascade');
            $table->foreignId('stagiaire_id')->constrained('stagiaires')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidature_stages');
    }
};
