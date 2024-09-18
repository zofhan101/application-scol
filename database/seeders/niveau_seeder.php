<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class niveau_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('niveau')->insert([
            ['nom_niveau'=>'L1', 'rang'=>'1'],
            ['nom_niveau'=>'L2', 'rang'=>'2'],
            ['nom_niveau'=>'L3', 'rang'=>'3'],
            ['nom_niveau'=>'4° année', 'rang'=>'4'],
            ['nom_niveau'=>'5° année', 'rang'=>'5'],
            ['nom_niveau'=>'6° année', 'rang'=>'6']
        ]);
    }
}
