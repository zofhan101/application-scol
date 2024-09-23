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
            ['nom_niveau'=>'PACES', 'rang'=>'1', 'nom_niveau_long'=>'Première Année Commune des Etudes de Santé'],
            ['nom_niveau'=>'NIVEAU L2', 'rang'=>'2'],
            ['nom_niveau'=>'NIVEAU L3', 'rang'=>'3'],
            ['nom_niveau'=>'QUATRIEME année', 'rang'=>'4'],
            ['nom_niveau'=>'CINQUIEME année', 'rang'=>'5'],
            ['nom_niveau'=>'SIXIEME année', 'rang'=>'6']
        ]);
    }
}
