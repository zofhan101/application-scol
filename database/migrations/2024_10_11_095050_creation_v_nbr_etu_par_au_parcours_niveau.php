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

            create or replace view v_cj_au_parcours_niveau as
            select *
            from au
            cross join parcours_niveau;
        ');

        DB::statement('


            create or replace view v_nbr_etu_par_au_parcours_niveau as
            select apn.id_au, apn.id_parcours, apn.id_niveau, count(id_inscription) as  nbr_inscrits
            from inscription as i
            join etudiants as e on i.id_etudiant = e.id_etudiants
            right join v_cj_au_parcours_niveau as apn on  i.id_au = apn.id_au and i.id_niveau = apn.id_niveau and e.id_parcours = apn.id_parcours
            group by apn.id_au, apn.id_parcours, apn.id_niveau


        ');


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nbr_etu_par_au_parcours_niveau;
        ');

        DB::statement('
            drop view if exists v_cj_au_parcours_niveau;
        ');
    }
};
