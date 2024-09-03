<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class AmdinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role')->insert(['nom_role'=>'admin']);
        DB::table('users')->insert([
            'name'=>'admin', 
            'email'=>'servicescol213@gmail.com', 
            'password'=>Hash::make('ScolAritE213#'), 
            'id_role'=>7]);

    }
}
