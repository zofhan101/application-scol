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
        Schema::create('cte_codes_barres_en_plus', function (Blueprint $table) {
            $table->id('id_cte_codes_barres_en_plus');
            $table->integer('cte_codes_barres_en_plus');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cte_codes_barres_en_plus');
    }
};
