<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class mention_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mention')->insert([
            ['nom_mention'=>'Médecine Humaine'],
            ['nom_mention'=>'Pharmacie'],
            ['nom_mention'=>'Médecine Vétérinaire'],
            ['nom_mention'=>'Sciences Paramédicales']
        ]);

        DB::table('parcours')->insert([
            ['nom_parcours'=>'Médecine Humaine', 'id_mention'=> 1],
            ['nom_parcours'=>'Pharmacie', 'id_mention'=> 2],
            ['nom_parcours'=>'Médecine Vétérinaire', 'id_mention'=> 3],
            ['nom_parcours'=>'Anesthésie', 'id_mention'=> 4],
            ['nom_parcours'=>'Electroradiologie', 'id_mention'=>4],
            ['nom_parcours'=>'Ergothérapie', 'id_mention'=>4],
            ['nom_parcours'=>'Maïeutique', 'id_mention'=>4],
            ['nom_parcours'=>'Massokinésithérapie', 'id_mention'=>4],
            ['nom_parcours'=>'Sciences Infirmières', 'id_mention'=>4],
            ['nom_parcours'=>'Technique d\'Appareillage et Orthopédie', 'id_mention'=>4],
            ['nom_parcours'=>'Technique de Laboratoire', 'id_mention'=>4],
        ]);

    }
}
