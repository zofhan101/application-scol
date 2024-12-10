<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('operation_par_deliberation', function (Blueprint $table) {
            $table->unique(['id_au', 'id_parcours', 'id_niveau']);

            $table->date('date_cloture_deliberation')->nullable()->change();

            $table->BigInteger('id_user_date_ouverture_deliberation');
            $table->foreign('id_user_date_ouverture_deliberation')->references('id')->on('users');

            $table->BigInteger('id_user_date_cloture_deliberation')->nullable();
            $table->foreign('id_user_date_cloture_deliberation')->references('id')->on('users');

        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operation_par_deliberation', function (Blueprint $table) {

            $table->dropUnique(['id_au', 'id_parcours', 'id_niveau']);
            $table->date('date_cloture_deliberation')->change();
            $table->dropColumn('id_user_date_ouverture_deliberation');
            $table->dropColumn('id_user_date_cloture_deliberation');
        });
    }
};
