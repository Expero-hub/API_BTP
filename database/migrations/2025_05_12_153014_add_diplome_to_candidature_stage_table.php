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
        Schema::table('candidature_stages', function (Blueprint $table) {
            //  $table->string('cv');
            //  $table->string('cip');
            //  $table->string('diplome');
            //  $table->string('lettre_motivation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('candidature_stage', function (Blueprint $table) {
            //
        });
    }
};
