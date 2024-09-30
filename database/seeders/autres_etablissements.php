<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class autres_etablissements extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('autres_etablissements')->insert([
            ['nom_autre_etablissement'=>'Faculté de Médecine d\'Antsiranana'],
            ['nom_autre_etablissement'=>'Faculté de Médecine de Mahajanga'],
            ['nom_autre_etablissement'=>'Faculté de Médecine de Toamasina'],
            ['nom_autre_etablissement'=>'Faculté de Médecine de Fianarantsoa'],
            ['nom_autre_etablissement'=>'Faculté de Médecine de Toliara']

        ]);
    }
}
