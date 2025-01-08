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
        create or replace view v_nombre_ue_validees_complet as
            select * from v_nombre_ue_validees
            union all
            select * from  v_nombre_ue_validees_sans_inscrits;

        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            drop view if exists v_nombre_ue_validees_complet;
        ');
    }
};
