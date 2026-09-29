<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->string('niveau', 2)->nullable()->after('categorie'); // CECRL : A1 à C2
            $table->string('sous_niveau')->nullable()->after('niveau'); // bas, moyen ou haut
        });
    }

    public function down(): void
    {
        Schema::table('questions', fn (Blueprint $table) => $table->dropColumn(['niveau', 'sous_niveau']));
    }
};
