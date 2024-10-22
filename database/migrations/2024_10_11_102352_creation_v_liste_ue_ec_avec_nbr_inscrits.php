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
            create or replace view v_liste_ue_ec_avec_nbr_inscrits as
            select l.*, n.nbr_inscrits
            from v_liste_ue_ec_avec_mentions as l
            join v_nbr_etu_par_au_parcours_niveau as n on l.id_parcours = n.id_parcours and l.id_niveau = n.id_niveau and l.id_au = n.id_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_liste_ue_ec_avec_nbr_inscrits
        ');
    }
};
