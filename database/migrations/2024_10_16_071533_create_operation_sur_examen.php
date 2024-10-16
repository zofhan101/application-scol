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
        Schema::create('operation_sur_examen', function (Blueprint $table) {
            $table->id('id_operation_sur_examen');
            $table->string('nom_operation_sur_examen');
            $table->timestamps();
        });

        Schema::create('date_operation', function (Blueprint $table) {
            $table->id('id_date_operation');
            $table->datetime('date_operation');
            $table->BigInteger('id_operation_sur_examen');
            $table->foreign('id_operation_sur_examen')->references('id_operation_sur_examen')->on('operation_sur_examen');
            $table->BigInteger('id_au');
            $table->foreign('id_au')->references('id_au')->on('au');
            $table->BigInteger('id_user_operation');
            $table->foreign('id_user_operation')->references('id')->on('uers');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('date_operation');
        Schema::dropIfExists('operation_sur_examen');
    }
};
