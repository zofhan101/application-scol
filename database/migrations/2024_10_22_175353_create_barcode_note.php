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
        Schema::create('barcode_note', function (Blueprint $table) {
            $table->BigInteger('id_ue_ec');
            $table->foreign('id_ue_ec')->references('id_ue_ec')->on('ue_ec_parcours_niveau_au');
            $table->integer('numero');
            $table->double('note');
            $table->primary(['id_ue_ec', 'numero']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcode_note');
    }
};
