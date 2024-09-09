<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles=[
            ['nom_role'=>'doyen'],
            ['nom_role'=>'vice-doyen'],
            ['nom_role'=>'secrétaire principal'],
            ['nom_role'=>'chef de service'],
            ['nom_role'=>'chef de division'],
            ['nom_role'=>'agent'],
        ];

        DB::table('role')->insert($roles);
    }
}
