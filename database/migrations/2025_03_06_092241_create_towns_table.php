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
        Schema::create('towns', function (Blueprint $table) {
            $table->id();
            $table->string("code_commune_insee")->nullable();
            $table->string("nom_de_la_commune")->nullable();
            $table->string("code_postal")->nullable();
            $table->string("libelle_d_acheminement")->nullable();
            $table->string("ligne_5")->nullable();
            $table->string("lat")->nullable();
            $table->string("lng")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('towns');
    }
};
