<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tentatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('epreuve_id')->nullable()->constrained()->cascadeOnDelete(); // null : test complet
            $table->json('questions'); // ids des questions, dans l'ordre du test
            $table->json('reponses'); // une entrée par question : index du choix, ou null
            $table->json('marquees'); // index des questions marquées « à revoir »
            $table->json('ecoutees'); // index des questions dont l'audio a déjà été lancé
            $table->unsignedSmallInteger('total');
            $table->timestamp('fin_chrono');
            $table->timestamp('terminee_le')->nullable();
            $table->unsignedSmallInteger('bonnes')->nullable();
            $table->string('niveau', 3)->nullable(); // NCLC estimé, null sous le NCLC 5
            $table->json('resultat')->nullable(); // détail par épreuve et par catégorie
            $table->timestamps();

            $table->index(['user_id', 'terminee_le']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tentatives');
    }
};
