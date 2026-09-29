<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('epreuves', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('code', 2)->unique(); // co, ce, ee, eo
            $table->string('nom');
            $table->string('description');
            $table->string('format'); // ex. « 39 questions · 35 min »
            $table->string('icone');
            $table->unsignedTinyInteger('ordre')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('epreuves');
    }
};
