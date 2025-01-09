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
        Schema::create('operation_par_import_resultat_paces', function (Blueprint $table) {
            $table->id('id_operation_par_import_resultat_paces');
            $table->date('date_import');
            $table->BigInteger('id_user_date_import');
            $table->foreign('id_user_date_import')->references('id')->on('users');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->BigInteger('id_parcours');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_par_import_resultat_paces');
    }
};
