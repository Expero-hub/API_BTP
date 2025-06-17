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
        Schema::create('candidature_projets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entreprise_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('projet_id')->constrained('projets')->onDelete('cascade');
            $table->string('motivation')->nullable();
            $table->string('statut')->default('en_attente'); // en_attente, acceptee, refusee
            $table->timestamps();

            $table->unique(['entreprise_id', 'projet_id']); // postulation unique par entreprise
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidature_projets');
    }
};
