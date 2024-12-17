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
        DB::statement('
            create materialized view v_inscrits2 as
            select i.id_au, e.id_parcours, i.id_niveau, i.id_inscription, e.id_etudiants, e.im, i.date_annulation, statut, a_passe_examen
            from inscription as i
            join etudiants as e on i.id_etudiant = e.id_etudiants;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop materialized view if exists v_inscrits2;
        ');
    }
};
