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
        Schema::table('resultats_definitifs', function (Blueprint $table) {
            $table->boolean('a_ete_delibere')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultats_definitifs', function (Blueprint $table) {
            $table->dropColumn('a_ete_delibere');
        });
    }
};
