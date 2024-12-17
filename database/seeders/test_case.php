<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class test_case extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('test_case')->insert([
            ["im"=>1, "nom_ue"=>'ue_1', "note"=>10, "valide"=>'V'],
            ["im"=>2, "nom_ue"=>'ue_1', "note"=>5, "valide"=>'E'],
            ["im"=>3, "nom_ue"=>'ue_1', "note"=>18, "valide"=>'V'],
            ["im"=>1, "nom_ue"=>'ue_2', "note"=>0, "valide"=>'E'],
            ["im"=>2, "nom_ue"=>'ue_2', "note"=>17, "valide"=>'V'],
            ["im"=>3, "nom_ue"=>'ue_2', "note"=>12, "valide"=>'V']
        ]);
    }
}
