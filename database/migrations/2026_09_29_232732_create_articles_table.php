<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // auteur
            $table->string('titre');
            $table->string('slug')->unique();
            $table->string('extrait', 300);
            $table->longText('contenu'); // HTML nettoyé
            $table->string('couverture')->nullable(); // chemin sur le disque public
            $table->string('categorie', 60);
            $table->unsignedSmallInteger('temps_lecture')->default(1); // en minutes
            $table->string('statut', 20)->default('brouillon');
            $table->timestamp('publie_le')->nullable();
            $table->string('meta_titre', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->index(['statut', 'publie_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
