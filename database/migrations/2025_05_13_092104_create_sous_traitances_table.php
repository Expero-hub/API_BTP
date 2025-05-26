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
        Schema::create('sous_traitances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained()->onDelete('cascade');
            $table->foreignId('entreprise_maitre_id')->constrained('entreprises')->onDelete('cascade');
            $table->foreignId('entreprise_sous_traitante_id')->nullable()->constrained('entreprises')->onDelete('cascade');
            $table->string('tache');
            $table->date('date_debut')->nullable();
            $table->date('date_fin');
            $table->enum('mode', ['appel', 'assignation'])->default('appel'); // appel = candidatures, assignation = ciblée
            $table->enum('statut', ['en_attente', 'confirmee', 'refusee'])->nullable(); // seulement utile si assignation
            $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sous_traitances');
    }
};
