<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class conditions_admission extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('moyenne_admission')->insert([
            ['moyenne_admission'=>10]
        ]);

        DB::table('pourcentage_admission')->insert([
            ['pourcentage_admission'=>75]
        ]);
    }
}
