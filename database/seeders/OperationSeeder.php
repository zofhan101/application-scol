<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('operation_sur_examen')->insert([
            ['nom_operation_sur_examen'=>'Saisie des notes'],
            ['nom_operation_sur_examen'=>'Vérification des notes'],
            ['nom_operation_sur_examen'=>'Saisie des en-têtes'],
            ['nom_operation_sur_examen'=>'Vérification des en-têtes'],
            ['nom_operation_sur_examen'=>'Résultats évaluation'],
            ['nom_operation_sur_examen'=>'Résultats Bruts de l\Année Universitaire'],
            ['nom_operation_sur_examen'=>'Liste de repêchage'],
            ['nom_operation_sur_examen'=>'Résultats avant délibération'],
            ['nom_operation_sur_examen'=>'Vérifcation des en-têtes'],
        ]);
    }
}
