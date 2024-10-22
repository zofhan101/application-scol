<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class nat_prov_serie extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('province')->insert([
            ['nom_province'=>'Antananarivo'],
            ['nom_province'=>'Antsiranana'],
            ['nom_province'=>'Toamasina'],
            ['nom_province'=>'Toliara'],
            ['nom_province'=>'Fianarantsoa'],
            ['nom_province'=>'Mahajanga']
        ]);

        DB::table('serie')->insert([
            ['nom_serie'=>'C'],
            ['nom_serie'=>'D'],
            ['nom_serie'=>'S'],
            ['nom_serie'=>'Techniques Agricoles'],
            ['nom_serie'=>'Techniques d\'Elevage']
        ]);

        DB::table('nationalites')->insert([
            ['nom_nationalite'=>'Malgache'],
            ['nom_nationalite'=>'Comorien'],
            ['nom_nationalite'=>'Camerounais'],
            ['nom_nationalite'=>'Egyptien'],
            ['nom_nationalite'=>'Lybanais'],
            ['nom_nationalite'=>'Autre']
        ]);

    }
}
