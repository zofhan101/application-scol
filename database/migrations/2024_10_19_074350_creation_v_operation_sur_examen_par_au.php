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
            create or replace view v_operation_par_examen_par_au as
            select o.*, e.id_session_examen, e.id_au
            from operation_par_examen as o
            join examen_par_au as e on o.id_examen_par_au = e.id_examen_par_au;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_operation_par_examen_par_au
        ');
    }
};
