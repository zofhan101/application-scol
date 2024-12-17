<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Eval_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('session_examen')->insert([
            ['nom_session_examen'=>'Evaluation 1', 'type_session'=>'eval'],
            ['nom_session_examen'=>'Evaluation 2', 'type_session'=>'eval'],
            ['nom_session_examen'=>'Concours PACES', 'type_session'=>'conc'],
            ['nom_session_examen'=>'Repechage', 'type_session'=>'repe'],


        ]);
    }
}
