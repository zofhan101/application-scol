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
        Schema::create('operation_par_examen', function (Blueprint $table) {
            $table->id('id_operation_par_examen');
            $table->date('date_ouverture_saisie_note')->nullable();
            $table->BigInteger('id_user_date_ouverture_saisie_note')->nullable();
            $table->foreign('id_user_date_ouverture_saisie_note')->references('id')->on('users');
            $table->date('date_cloture_saisie_note')->nullable();
            $table->BigInteger('id_user_date_cloture_saisie_note')->nullable();
            $table->foreign('id_user_date_cloture_saisie_note')->references('id')->on('users');
            $table->date('date_ouverture_verification_note')->nullable();
            $table->BigInteger('id_user_date_ouverture_verification_note')->nullable();
            $table->foreign('id_user_date_ouverture_verification_note')->references('id')->on('users');
            $table->date('date_cloture_verification_note')->nullable();
            $table->BigInteger('id_user_date_cloture_verification_note')->nullable();
            $table->foreign('id_user_date_cloture_verification_note')->references('id')->on('users');
            $table->date('date_ouverture_saisie_en_tete')->nullable();
            $table->BigInteger('id_user_date_ouverture_saisie_en_tete')->nullable();
            $table->foreign('id_user_date_ouverture_saisie_en_tete')->references('id')->on('users');
            $table->date('date_cloture_saisie_en_tete')->nullable();
            $table->BigInteger('id_user_date_cloture_saisie_en_tete')->nullable();
            $table->foreign('id_user_date_cloture_saisie_en_tete')->references('id')->on('users');
            $table->date('date_ouverture_verification_en_tete')->nullable();
            $table->BigInteger('id_user_date_ouverture_verification_en_tete')->nullable();
            $table->foreign('id_user_date_ouverture_verification_en_tete')->references('id')->on('users');
            $table->date('date_cloture_verification_en_tete')->nullable();
            $table->BigInteger('id_user_date_cloture_verification_en_tete')->nullable();
            $table->foreign('id_user_date_cloture_verification_en_tete')->references('id')->on('users');
            $table->date('date_resultats')->nullable();
            $table->BigInteger('id_user_date_resultats')->nullable();
            $table->foreign('id_user_date_resultats')->references('id')->on('users');
            $table->BigInteger('id_examen_par_au')->unique();
            $table->foreign('id_examen_par_au')->references('id_examen_par_au')->on('examen_par_au');

            $table->timestamps();
        });

        Schema::create('operation_sur_examen_par_au', function (Blueprint $table) {
            $table->id('id_operation_sur_examen_par_au');
            $table->date('date_liste_repechage')->nullable();
            $table->BigInteger('id_user_date_liste_repechage')->nullable();
            $table->foreign('id_user_date_liste_repechage')->references('id')->on('users');
            $table->date('date_resultat_avant_deliberation')->nullable();
            $table->BigInteger('id_user_date_resultat_avant_deliberation')->nullable();
            $table->foreign('id_user_date_resultat_avant_deliberation')->references('id')->on('users');
            $table->date('date_deliberation')->nullable();
            $table->BigInteger('id_user_date_deliberation')->nullable();
            $table->foreign('id_user_date_deliberation')->references('id')->on('users');
            $table->date('date_resultat_definitif')->nullable();
            $table->BigInteger('id_user_date_resultat_definitif')->nullable();
            $table->foreign('id_user_date_resultat_definitif')->references('id')->on('users');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operation_par_examen');
        Schema::dropIfExists('operation_sur_examen_par_au');
    }
};
