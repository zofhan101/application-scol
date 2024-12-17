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
        Schema::create('operation_par_au', function (Blueprint $table) {
            $table->id('id_operation_par_au');
            $table->BigInteger('id_au')->unique();
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->date('date_resultats_avant_repechage')->nullable();
            $table->BigInteger('id_user_date_resultats_avant_repechage')->nullable();
            $table->foreign('id_user_date_resultats_avant_repechage')->references('id')->on('users');
            $table->date('date_liste_repechage')->nullable();
            $table->BigInteger('id_user_date_liste_repechage')->nullable();
            $table->foreign('id_user_date_liste_repechage')->references('id')->on('users');
            $table->date('date_resultats_avant_deliberation')->nullable();
            $table->BigInteger('id_user_date_resultats_avant_deliberation')->nullable();
            $table->foreign('id_user_date_resultats_avant_deliberation')->references('id')->on('users');
            $table->date('date_resultats_definitifs')->nullable();
            $table->BigInteger('id_user_date_resultats_definitifs')->nullable();
            $table->foreign('id_user_date_resultats_definitifs')->references('id')->on('users');
            $table->timestamps();
        });


        Schema::create('operation_par_deliberation', function (Blueprint $table) {
            $table->id('id_operation_sur_deliberation');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->BigInteger('id_parcours');
            $table->foreign('id_parcours')->references('id_parcours')->on('parcours');
            $table->BigInteger('id_niveau');
            $table->foreign('id_niveau')->references('id_niveau')->on('niveau');
            $table->date('date_ouverture_deliberation');
            $table->date('date_cloture_deliberation');






        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_par_deliberation');
        Schema::dropIfExists('operation_par_au');
    }
};
