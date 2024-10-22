<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $roles=[
            ['nom_role'=>'admin','rang_role'=>70],
            ['nom_role'=>'doyen','rang_role'=>60],
            ['nom_role'=>'vice-doyen','rang_role'=>50],
            ['nom_role'=>'secrétaire principal','rang_role'=>40],
            ['nom_role'=>'chef de service','rang_role'=>30],
            ['nom_role'=>'adjoint','rang_role'=>20],
            ['nom_role'=>'chef de division scolarite','rang_role'=>10],
            ['nom_role'=>'chef de division','rang_role'=>0],
        ];

        DB::table('role')->insert($roles);

        DB::table('users')->insert([
            'name'=>'admin',
            'email'=>'servicescol213@gmail.com',
            'password'=>Hash::make('ScolAritE213#'),
            'id_role'=>1]);



    }
}
