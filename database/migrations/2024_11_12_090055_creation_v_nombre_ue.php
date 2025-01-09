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
            create or replace view v_nombre_ue as
                select id_au, id_parcours, id_niveau, count(id_unite_enseignement) as nombre_ue
                from v_liste_ue
                group by id_au, id_parcours, id_niveau;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue;
        ');
    }
};
