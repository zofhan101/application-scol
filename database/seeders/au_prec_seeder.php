<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class au_prec_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('au')->insert([
            ['intitule'=>'2023-2024', 'ouverture'=>date('Y-m-d'), 'cloture'=>date('Y-m-d')]
        ]);
    }
}
