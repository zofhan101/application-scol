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
            ['nom_session_examen'=>'Evaluation 1'],
            ['nom_session_examen'=>'Evaluation 2']

        ]);
    }
}
