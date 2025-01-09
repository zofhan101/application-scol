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
            create or replace view v_correspondance_note_matricule as
            select m.id_ue_ec as id_ue_ec_matricule, m.numero as numero_matricule, m.matricule, n.id_ue_ec as id_ue_ec_note, n.numero as numero_note, n.note, ue_ec.*
            from barcode_note as n
            full join barcode_matricule as m on n.id_ue_ec = m.id_ue_ec and n.numero = m.numero
            join ue_ec_parcours_niveau_au as ue_ec on n.id_ue_ec = ue_ec.id_ue_ec or m.id_ue_ec = ue_ec.id_ue_ec;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('drop view if exists v_correspondance_note_matricule');
    }
};
