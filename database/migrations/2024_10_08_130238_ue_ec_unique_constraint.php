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
        Schema::table('ue_ec_parcours_niveau_au', function (Blueprint $table) {
            $table->unique(['id_examen_par_au', 'id_parcours', 'id_niveau', 'id_unite_enseignement', 'id_element_constitutif', 'id_au']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ue_ec_parcours_niveau_au', function (Blueprint $table) {
            $table->dropUnique(['id_examen_par_au', 'id_parcours', 'id_niveau', 'id_unite_enseignement', 'id_element_constitutif', 'id_au']);
        });

    }
};
