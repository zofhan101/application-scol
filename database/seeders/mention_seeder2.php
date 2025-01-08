<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class mention_seeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mention_ent')->insert([
            ['nom_mention_ent'=>'Médecine Humaine'],
            ['nom_mention_ent'=>'Médecine Vétérinaire'],
            ['nom_mention_ent'=>'Pharmacie'],
            ['nom_mention_ent'=>'Sciences Paramédicales']
        ]);


        DB::table('parcours_ent')->insert([
            ['nom_parcours_ent'=>'Sciences Infirmières'],
            ['nom_parcours_ent'=>'Anesthésie'],
            ['nom_parcours_ent'=>'Electroradiologie'],
            ['nom_parcours_ent'=>'Technique d\'Appareillage et Orthopédie'],
            ['nom_parcours_ent'=>'Technique de Laboratoire'],
            ['nom_parcours_ent'=>'Maïeutique'],
            ['nom_parcours_ent'=>'Ergothérapie'],
            ['nom_parcours_ent'=>'Massokinésithérapie'],
        ]);

        DB::table('correspondance_mention_ent')->insert([
            ['id_mention'=>1,'id_mention_ent'=>1],
            ['id_mention'=>2,'id_mention_ent'=>3],
            ['id_mention'=>3,'id_mention_ent'=>2],
            ['id_mention'=>4,'id_mention_ent'=>4],
        ]);


        DB::table('correspondance_parcours_ent')->insert([
            ['id_parcours'=>4,'id_parcours_ent'=>2],
            ['id_parcours'=>5,'id_parcours_ent'=>3],
            ['id_parcours'=>6,'id_parcours_ent'=>7],
            ['id_parcours'=>7,'id_parcours_ent'=>6],
            ['id_parcours'=>8,'id_parcours_ent'=>8],
            ['id_parcours'=>9,'id_parcours_ent'=>1],
            ['id_parcours'=>10,'id_parcours_ent'=>4],
            ['id_parcours'=>11,'id_parcours_ent'=>5],

        ]);
    }
}
