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
            ['nom_niveau'=>'PACES', 'rang'=>'1', 'nom_niveau_long'=>'Première Année Commune des Etudes de Santé','cycle' => '1']
        ]);

        DB::table('niveau')->insert([
            ['nom_niveau'=>'NIVEAU L2', 'rang'=>'2','cycle' => '1'],
            ['nom_niveau'=>'NIVEAU L3', 'rang'=>'3','cycle' => '1'],
            ['nom_niveau'=>'QUATRIEME année', 'rang'=>'4','cycle' => '2'],
            ['nom_niveau'=>'CINQUIEME année', 'rang'=>'5','cycle' => '2'],
            ['nom_niveau'=>'SIXIEME année', 'rang'=>'6','cycle' => '2']
        ]);


        //niveaux existants par parcours

        DB::table('parcours_niveau')->insert([
            ['id_parcours'=>1, 'id_niveau'=>1],
            ['id_parcours'=>1, 'id_niveau'=>2],
            ['id_parcours'=>1, 'id_niveau'=>3],
            ['id_parcours'=>1, 'id_niveau'=>4],
            ['id_parcours'=>1, 'id_niveau'=>5],
            ['id_parcours'=>1, 'id_niveau'=>6],

            ['id_parcours'=>2, 'id_niveau'=>1],
            ['id_parcours'=>2, 'id_niveau'=>2],
            ['id_parcours'=>2, 'id_niveau'=>3],
            ['id_parcours'=>2, 'id_niveau'=>4],
            ['id_parcours'=>2, 'id_niveau'=>5],
            ['id_parcours'=>2, 'id_niveau'=>6],

            ['id_parcours'=>3, 'id_niveau'=>1],
            ['id_parcours'=>3, 'id_niveau'=>2],
            ['id_parcours'=>3, 'id_niveau'=>3],
            ['id_parcours'=>3, 'id_niveau'=>4],
            ['id_parcours'=>3, 'id_niveau'=>5],
            ['id_parcours'=>3, 'id_niveau'=>6],

            ['id_parcours'=>4, 'id_niveau'=>1],
            ['id_parcours'=>4, 'id_niveau'=>2],
            ['id_parcours'=>4, 'id_niveau'=>3],

            ['id_parcours'=>5, 'id_niveau'=>1],
            ['id_parcours'=>5, 'id_niveau'=>2],
            ['id_parcours'=>5, 'id_niveau'=>3],

            ['id_parcours'=>6, 'id_niveau'=>1],
            ['id_parcours'=>6, 'id_niveau'=>2],
            ['id_parcours'=>6, 'id_niveau'=>3],

            ['id_parcours'=>7, 'id_niveau'=>1],
            ['id_parcours'=>7, 'id_niveau'=>2],
            ['id_parcours'=>7, 'id_niveau'=>3],

            ['id_parcours'=>8, 'id_niveau'=>1],
            ['id_parcours'=>8, 'id_niveau'=>2],
            ['id_parcours'=>8, 'id_niveau'=>3],

            ['id_parcours'=>9, 'id_niveau'=>1],
            ['id_parcours'=>9, 'id_niveau'=>2],
            ['id_parcours'=>9, 'id_niveau'=>3],

            ['id_parcours'=>10, 'id_niveau'=>1],
            ['id_parcours'=>10, 'id_niveau'=>2],
            ['id_parcours'=>10, 'id_niveau'=>3],

            ['id_parcours'=>11, 'id_niveau'=>1],
            ['id_parcours'=>11, 'id_niveau'=>2],
            ['id_parcours'=>11, 'id_niveau'=>3]
        ]);


        //transferts autorisés

        DB::table('transferts_autorises')->insert([
            ['id_parcours'=>1, 'id_niveau'=>4],
            ['id_parcours'=>1, 'id_niveau'=>5],
            ['id_parcours'=>1, 'id_niveau'=>6],

            ['id_parcours'=>2, 'id_niveau'=>4],
            ['id_parcours'=>2, 'id_niveau'=>5],
            ['id_parcours'=>2, 'id_niveau'=>6],

            ['id_parcours'=>3, 'id_niveau'=>4],
            ['id_parcours'=>3, 'id_niveau'=>5],
            ['id_parcours'=>3, 'id_niveau'=>6],

            ['id_parcours'=>3, 'id_niveau'=>4],
            ['id_parcours'=>3, 'id_niveau'=>5],
            ['id_parcours'=>3, 'id_niveau'=>6],

            ['id_parcours'=>4, 'id_niveau'=>2],
            ['id_parcours'=>4, 'id_niveau'=>3],


            ['id_parcours'=>5, 'id_niveau'=>2],
            ['id_parcours'=>5, 'id_niveau'=>3],

            ['id_parcours'=>6, 'id_niveau'=>2],
            ['id_parcours'=>6, 'id_niveau'=>3],

            ['id_parcours'=>7, 'id_niveau'=>2],
            ['id_parcours'=>7, 'id_niveau'=>3],

            ['id_parcours'=>8, 'id_niveau'=>2],
            ['id_parcours'=>8, 'id_niveau'=>3],

            ['id_parcours'=>9, 'id_niveau'=>2],
            ['id_parcours'=>9, 'id_niveau'=>3],

            ['id_parcours'=>10, 'id_niveau'=>2],
            ['id_parcours'=>10, 'id_niveau'=>3],

            ['id_parcours'=>11, 'id_niveau'=>2],
            ['id_parcours'=>11, 'id_niveau'=>3]


        ]);


    }
}
