<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('epreuve_id')->constrained()->cascadeOnDelete();
            $table->string('enonce');
            $table->text('support')->nullable(); // document à lire (compréhension écrite)
            $table->string('audio')->nullable(); // chemin du fichier audio (compréhension orale)
            $table->unsignedSmallInteger('duree_audio')->nullable(); // en secondes
            $table->text('transcription')->nullable();
            $table->json('choix');
            $table->unsignedTinyInteger('bonne_reponse'); // index dans « choix »
            $table->string('feedback')->nullable();
            $table->text('explication');
            $table->unsignedSmallInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
