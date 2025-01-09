<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class note_max_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('note_max')->insert([
            ['note_max'=>20]
        ]);

        DB::table('note_eliminatoire')->insert([
            ['note_elim'=>5]
        ]);
    }
}
