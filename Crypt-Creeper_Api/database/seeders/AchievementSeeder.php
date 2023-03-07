<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AchievementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('achievements')->insert([
            'name' => "Armed to kill",
            'description' => "Obtain a level 5 sword",
            'icon_name' => "ICON_SWORD_5"
        ]);
        DB::table('achievements')->insert([
            'name' => "Protector",
            'description' => "Obtain a level 5 shield",
            'icon_name' => "ICON_SHIELD_5"
        ]);
        DB::table('achievements')->insert([
            'name' => "By the skin of your teeth",
            'description' => "Kill the boss with 1HP left",
            'icon_name' => "ICON_ENEMY_8"
        ]);
        DB::table('achievements')->insert([
            'name' => "Pension plan",
            'description' => "Get 100 coins",
            'icon_name' => "ICON_ENTITY_SHOP"
        ]);
        DB::table('achievements')->insert([
            'name' => "Enlightened",
            'description' => "Get 200 XP",
            'icon_name' => "ICON_ENTITY_TEMPLE"
        ]);
        DB::table('achievements')->insert([
            'name' => "Cleaner",
            'description' => "Leave an empty floor",
            'icon_name' => "ICON_ENTITY_PORTAL"
        ]);
        DB::table('achievements')->insert([
            'name' => "Armed to the teeth",
            'description' => "Equip a level 5 sword and level 5 shield",
            'icon_name' => "ICON_ENTITY_PLAYER"
        ]);
        DB::table('achievements')->insert([
            'name' => "Healthcare",
            'description' => "Reach max health (6)",
            'icon_name' => "ICON_C_MAXHEALTH"
        ]);
        DB::table('achievements')->insert([
            'name' => "Deep pockets",
            'description' => "Reach 3 inventory slots",
            'icon_name' => "ICON_C_SLOT"
        ]);
        DB::table('achievements')->insert([
            'name' => "Almost got it",
            'description' => "Be defeated by the boss",
            'icon_name' => "ICON_ENEMY_8"
        ]);
        DB::table('achievements')->insert([
            'name' => "Not even trying",
            'description' => "Die in the first floor",
            'icon_name' => "ICON_ENEMY_1"
        ]);
        DB::table('achievements')->insert([
            'name' => "Unscathed",
            'description' => "Beat the 20 floors without losing HP",
            'icon_name' => "ICON_ENTITY_PLAYER"
        ]);
        DB::table('achievements')->insert([
            'name' => "Better odds",
            'description' => "Use a reroll potion",
            'icon_name' => "ICON_C_RERROLL"
        ]);
        DB::table('achievements')->insert([
            'name' => "Pick-me-up",
            'description' => "Use a max health potion",
            'icon_name' => "ICON_C_MAXHEALTH"
        ]);
        DB::table('achievements')->insert([
            'name' => "Powder",
            'description' => "Use a bomb",
            'icon_name' => "ICON_C_BOMB"
        ]);
        DB::table('achievements')->insert([
            'name' => "Turned into frog",
            'description' => "Use a green potion",
            'icon_name' => "ICON_C_GREENP"
        ]);
        DB::table('achievements')->insert([
            'name' => "Hi-Score!",
            'description' => "Obtain a 6 digit score",
            'icon_name' => "ICON_ENTITY_PLAYER"
        ]);
    }
}
