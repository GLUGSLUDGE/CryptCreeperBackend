<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('faction')->insert([
            'name' => 'Ghost',
            'icon' => 'icon guay'
        ]);
        DB::table('faction')->insert([
            'name' => 'Hans',
            'icon' => 'mano gay'
        ]);
        DB::table('faction')->insert([
            'name' => 'Mosca',
            'icon' => 'a'
        ]);
        DB::table('faction')->insert([
            'name' => 'Double Double',
            'icon' => 'b'
        ]);
        DB::table('faction')->insert([
            'name' => 'Uzzi',
            'icon' => 'c'
        ]);
        DB::table('faction')->insert([
            'name' => 'Tia',
            'icon' => 'c'
        ]);
        DB::table('faction')->insert([
            'name' => 'King Eyes',
            'icon' => 'b'
        ]);
        DB::table('faction')->insert([
            'name' => 'Big Mud',
            'icon' => 'b'
        ]);
    }
}
