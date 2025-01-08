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
            create or replace view v_parcours_niveau as
            select p.id_parcours, p.nom_parcours, n.id_niveau, n.nom_niveau, n.rang
            from parcours as p
            join parcours_niveau as pn on pn.id_parcours = p.id_parcours
            join niveau as n on pn.id_niveau = n.id_niveau;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(' drop view if exists v_parcours_niveau');
    }
};
