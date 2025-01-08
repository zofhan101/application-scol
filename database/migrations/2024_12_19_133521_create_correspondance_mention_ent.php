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
        Schema::create('correspondance_mention_ent', function (Blueprint $table) {
            $table->id('id_correspondance_mention_ent');
            $table->BigInteger('id_mention');
            $table->BigInteger('id_mention_ent');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correspondance_mention_ent');
    }
};
