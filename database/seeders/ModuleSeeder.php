<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('modules')->insert([
            [
                'id' => 1,
                'name' => 'URL Shortener',
                'description' => 'Raccourcir et gérer des liens',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Wallet',
                'description' => 'Gestion du solde et transferts',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Marketplace + Stock Manager',
                'description' => 'Gestion de produits et commandes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Time Tracker',
                'description' => 'Suivi du temps et sessions',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Investment Tracker',
                'description' => 'Suivi du portefeuille d\'investissement',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
