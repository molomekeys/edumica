<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Une table `articles` héritée d'un déploiement interrompu peut avoir
        // été « complétée » sans recevoir toutes ses colonnes : on ajoute
        // uniquement celles qui manquent.
        if (! Schema::hasTable('articles')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            if (! Schema::hasColumn('articles', 'user_id')) {
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('articles', 'couverture')) {
                $table->string('couverture')->nullable();
            }
            if (! Schema::hasColumn('articles', 'temps_lecture')) {
                $table->unsignedSmallInteger('temps_lecture')->default(1);
            }
            if (! Schema::hasColumn('articles', 'statut')) {
                $table->string('statut', 20)->default('brouillon');
            }
            if (! Schema::hasColumn('articles', 'publie_le')) {
                $table->timestamp('publie_le')->nullable();
            }
            if (! Schema::hasColumn('articles', 'meta_titre')) {
                $table->string('meta_titre', 70)->nullable();
            }
            if (! Schema::hasColumn('articles', 'meta_description')) {
                $table->string('meta_description', 160)->nullable();
            }
        });

        if (! Schema::hasIndex('articles', ['statut', 'publie_le'])) {
            Schema::table('articles', fn (Blueprint $table) => $table->index(['statut', 'publie_le']));
        }
    }

    public function down(): void
    {
        //
    }
};
