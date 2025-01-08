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

        Schema::table('examen_par_au', function (Blueprint $table) {
            $table->unique(['id_au', 'id_session_examen']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('examen_par_au', function (Blueprint $table) {
            $table->dropUnique(['id_au', 'id_session_examen']);
        });
    }
};
