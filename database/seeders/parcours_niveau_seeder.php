<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class parcours_niveau_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
            ['id_parcours'=>2, 'id_niveau'=>6],

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
    }
}
