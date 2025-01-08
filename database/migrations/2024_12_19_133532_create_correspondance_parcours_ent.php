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
        Schema::create('correspondance_parcours_ent', function (Blueprint $table) {
            $table->id('id_correspondance_parcours_ent');
            $table->BigInteger('id_parcours');
            $table->BigInteger('id_parcours_ent');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondance_parcours_ent');
    }
};
