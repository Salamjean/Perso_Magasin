<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Compte Administrateur Unique
        User::create([
            'name' => 'Directeur',
            'firstname' => 'Alain',
            'email' => 'admin@gmail.com',
            'phone' => '07000001',
            'address' => 'Plateau, Abidjan',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('azertyui'),
        ]);

        // 2. Paramètres de base du magasin
        Setting::set('store_name', 'GestMagasin');
        Setting::set('store_phone', '07000001');
        Setting::set('store_email', 'contact@gestmagasin.com');
        Setting::set('store_address', 'Abidjan, Côte d\'Ivoire');
        Setting::set('currency', 'FCFA');
        Setting::set('default_vat_rate', 18, 'float');
        Setting::set('receipt_footer', 'Merci pour votre visite et à très bientôt !');
        Setting::set('low_stock_global_threshold', 10, 'integer');
    }
}
