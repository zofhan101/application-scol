<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class cte_code_barre_plus extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('cte_codes_barres_en_plus')->insert([
            ['cte_codes_barres_en_plus'=>10]
        ]);
    }
}
