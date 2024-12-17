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
        Schema::table('barcode_matricule', function (Blueprint $table) {
            $table->index(['id_ue_ec', 'verifie']);
        });

        Schema::table('barcode_note', function (Blueprint $table) {
            $table->index(['id_ue_ec', 'verifie']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barcode_note', function (Blueprint $table) {
            $table->dropIndex(['id_ue_ec', 'verifie']);
        });

        Schema::table('barcode_matricule', function (Blueprint $table) {
            $table->dropIndex(['id_ue_ec', 'verifie']);
        });
    }
};
