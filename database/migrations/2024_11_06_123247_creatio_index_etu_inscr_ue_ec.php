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
        Schema::table('inscription', function (Blueprint $table) {
            $table->index(['id_au', 'id_niveau']);
        });

        Schema::table('etudiants', function (Blueprint $table) {
            $table->index('id_parcours');
            $table->index('im');
        });

        Schema::table('ue_ec_parcours_niveau_au', function (Blueprint $table) {
            $table->index(['id_parcours', 'id_niveau', 'id_au']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ue_ec_parcours_niveau_au', function (Blueprint $table) {
            $table->dropIndex(['id_parcours', 'id_niveau', 'id_au']);
        });

        Schema::table('etudiants', function (Blueprint $table) {
            $table->dropIndex(['id_parcours']);
            $table->dropIndex(['im']);
        });

        Schema::table('inscription', function (Blueprint $table) {
            $table->dropIndex(['id_au', 'id_niveau']);
        });
    }
};
