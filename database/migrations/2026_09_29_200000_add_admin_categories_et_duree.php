<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('password');
            $table->string('google_id')->nullable()->unique()->after('is_admin'); // pour la future connexion Google
            $table->string('avatar')->nullable()->after('google_id');
        });

        Schema::table('epreuves', function (Blueprint $table) {
            $table->unsignedSmallInteger('duree_test')->nullable()->after('format'); // durée du test blanc, en minutes
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->string('categorie')->default('Général')->after('epreuve_id');
        });
    }

    public function down(): void
    {
        Schema::table('questions', fn (Blueprint $table) => $table->dropColumn('categorie'));
        Schema::table('epreuves', fn (Blueprint $table) => $table->dropColumn('duree_test'));
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn(['is_admin', 'google_id', 'avatar']);
        });
    }
};
