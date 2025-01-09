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
            create or replace view  v_inscrits as
                select e.*,i.id_inscription, i.date_inscription, i.date_annulation, i.id_niveau, i.id_au,n.nom_niveau,au.intitule, p.nom_parcours, m.id_mention, m.nom_mention, i.date_certificat_scol
                from etudiants as e
                join inscription as i on i.id_etudiant = e.id_etudiants
                join niveau as n on i.id_niveau = n.id_niveau
                join au on i.id_au = au.id_au
                join parcours as p on e.id_parcours =  p.id_parcours
                join mention as m on p.id_mention = m.id_mention;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_inscrits;
        ');
    }
};
