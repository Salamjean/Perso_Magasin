<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Utilisateurs pour chaque rôle
        $admin = User::create([
            'name' => 'Directeur',
            'firstname' => 'Alain',
            'email' => 'admin@gestmagasin.com',
            'phone' => '+225 07 00 00 01',
            'address' => 'Plateau, Abidjan',
            'role' => 'admin',
            'status' => 'active',
            'password' => Hash::make('admin123'),
        ]);

        $magasinier = User::create([
            'name' => 'Traoré',
            'firstname' => 'Bakary',
            'email' => 'magasinier@gestmagasin.com',
            'phone' => '+225 07 00 00 02',
            'address' => 'Yopougon, Abidjan',
            'role' => 'magasinier',
            'status' => 'active',
            'password' => Hash::make('magasinier123'),
        ]);

        $caissier = User::create([
            'name' => 'Koffi',
            'firstname' => 'Jean',
            'email' => 'caissier@gestmagasin.com',
            'phone' => '+225 07 00 00 03',
            'address' => 'Cocody, Abidjan',
            'role' => 'caissier',
            'status' => 'active',
            'password' => Hash::make('caissier123'),
        ]);

        $livreur = User::create([
            'name' => 'Koné',
            'firstname' => 'Moussa',
            'email' => 'livreur@gestmagasin.com',
            'phone' => '+225 07 00 00 04',
            'address' => 'Marcory, Abidjan',
            'role' => 'livreur',
            'status' => 'active',
            'password' => Hash::make('livreur123'),
        ]);

        // 2. Paramètres du magasin
        Setting::set('store_name', 'Supermarché GestMagasin Prestige');
        Setting::set('store_phone', '+225 27 22 00 00 00');
        Setting::set('store_email', 'contact@gestmagasin.com');
        Setting::set('store_address', 'Boulevard Latrille, Cocody Angré 8e Tranche, Abidjan');
        Setting::set('currency', 'FCFA');
        Setting::set('default_vat_rate', 18, 'float');
        Setting::set('receipt_footer', 'Merci pour votre visite et à très bientôt !');
        Setting::set('low_stock_global_threshold', 10, 'integer');

        // 3. Catégories
        $categoriesData = [
            ['name' => 'Boissons & Jus', 'slug' => 'boissons-jus', 'color' => '#3b82f6', 'description' => 'Eaux minérales, sodas, jus de fruits naturels et boissons énergisantes'],
            ['name' => 'Alimentation & Épicerie', 'slug' => 'alimentation-epicerie', 'color' => '#10b981', 'description' => 'Riz, pâtes alimentaires, huiles, conserves et sauces'],
            ['name' => 'Produits Laitiers & Frais', 'slug' => 'produits-laitiers-frais', 'color' => '#f59e0b', 'description' => 'Lait, fromages, yaourts, beurre et crèmes'],
            ['name' => 'Hygiène & Beauté', 'slug' => 'hygiene-beaute', 'color' => '#ec4899', 'description' => 'Savons, shampoings, dentifrices, soins du corps'],
            ['name' => 'Produits Ménagers & Entretien', 'slug' => 'produits-menagers', 'color' => '#8b5cf6', 'description' => 'Lessives, détergents, nettoyants sol et désinfectants'],
            ['name' => 'Boulangerie & Pâtisserie', 'slug' => 'boulangerie-patisserie', 'color' => '#ef4444', 'description' => 'Pains frais, croissants, gâteaux et viennoiseries'],
        ];

        $categories = [];
        foreach ($categoriesData as $cData) {
            $categories[$cData['slug']] = Category::create($cData);
        }

        // 4. Fournisseurs
        $supplier1 = Supplier::create([
            'company_name' => 'Solibra CI Distribution',
            'contact_name' => 'Kouadio Michel',
            'phone' => '+225 01 02 03 04 05',
            'email' => 'commandes@solibra.ci',
            'address' => 'Zone Industrielle de Vridi',
            'city' => 'Abidjan',
            'tax_number' => 'CI-ABJ-2023-B-1234',
        ]);

        $supplier2 = Supplier::create([
            'company_name' => 'Agro-Alimentaire Ivoire SARL',
            'contact_name' => 'Soro Amadou',
            'phone' => '+225 05 06 07 08 09',
            'email' => 'ventes@agro-ivoire.ci',
            'address' => 'Zone Industrielle de Yopougon',
            'city' => 'Abidjan',
            'tax_number' => 'CI-ABJ-2022-A-5678',
        ]);

        $supplier3 = Supplier::create([
            'company_name' => 'Hygiène & Clean Afrique',
            'contact_name' => 'Aya Virginie',
            'phone' => '+225 07 11 22 33 44',
            'email' => 'contact@clean-afrique.com',
            'address' => 'Koumassi Boulevard de Marseille',
            'city' => 'Abidjan',
            'tax_number' => 'CI-ABJ-2024-C-9988',
        ]);

        // 5. Produits
        $productsData = [
            [
                'reference' => 'PRD-001',
                'barcode' => '61811001001',
                'name' => 'Coca-Cola Canette 33cl',
                'category_id' => $categories['boissons-jus']->id,
                'brand' => 'Coca-Cola',
                'unit' => 'canette',
                'buy_price' => 350,
                'sell_price' => 500,
                'stock_quantity' => 120,
                'alert_threshold' => 20,
                'supplier_id' => $supplier1->id,
                'expiration_date' => Carbon::now()->addMonths(8),
            ],
            [
                'reference' => 'PRD-002',
                'barcode' => '61811001002',
                'name' => 'Eau Minérale Céleste 1,5L',
                'category_id' => $categories['boissons-jus']->id,
                'brand' => 'Céleste',
                'unit' => 'bouteille',
                'buy_price' => 250,
                'sell_price' => 400,
                'stock_quantity' => 4, // Rupture / Alerte
                'alert_threshold' => 15,
                'supplier_id' => $supplier1->id,
                'expiration_date' => Carbon::now()->addMonths(12),
            ],
            [
                'reference' => 'PRD-003',
                'barcode' => '61811001003',
                'name' => 'Riz Parfumé Dinor 5kg',
                'category_id' => $categories['alimentation-epicerie']->id,
                'brand' => 'Dinor',
                'unit' => 'sac',
                'buy_price' => 4200,
                'sell_price' => 5500,
                'stock_quantity' => 45,
                'alert_threshold' => 10,
                'supplier_id' => $supplier2->id,
                'expiration_date' => Carbon::now()->addMonths(18),
            ],
            [
                'reference' => 'PRD-004',
                'barcode' => '61811001004',
                'name' => 'Huile Végétale Dinor 1L',
                'category_id' => $categories['alimentation-epicerie']->id,
                'brand' => 'Dinor',
                'unit' => 'bouteille',
                'buy_price' => 1100,
                'sell_price' => 1400,
                'stock_quantity' => 8, // Bientôt en rupture
                'alert_threshold' => 10,
                'supplier_id' => $supplier2->id,
                'expiration_date' => Carbon::now()->addMonths(14),
            ],
            [
                'reference' => 'PRD-005',
                'barcode' => '61811001005',
                'name' => 'Lait Bonnet Rouge 400g',
                'category_id' => $categories['produits-laitiers-frais']->id,
                'brand' => 'Bonnet Rouge',
                'unit' => 'boite',
                'buy_price' => 1800,
                'sell_price' => 2300,
                'stock_quantity' => 30,
                'alert_threshold' => 8,
                'supplier_id' => $supplier2->id,
                'expiration_date' => Carbon::now()->addDays(12), // Bientôt expiré
            ],
            [
                'reference' => 'PRD-006',
                'barcode' => '61811001006',
                'name' => 'Savon de Toilette Lux 100g',
                'category_id' => $categories['hygiene-beaute']->id,
                'brand' => 'Lux',
                'unit' => 'pcs',
                'buy_price' => 300,
                'sell_price' => 450,
                'stock_quantity' => 85,
                'alert_threshold' => 15,
                'supplier_id' => $supplier3->id,
                'expiration_date' => Carbon::now()->addMonths(24),
            ],
            [
                'reference' => 'PRD-007',
                'barcode' => '61811001007',
                'name' => 'Lessive Poudre OMO 1kg',
                'category_id' => $categories['produits-menagers']->id,
                'brand' => 'OMO',
                'unit' => 'sachet',
                'buy_price' => 1200,
                'sell_price' => 1600,
                'stock_quantity' => 0, // Rupture totale
                'alert_threshold' => 10,
                'supplier_id' => $supplier3->id,
                'expiration_date' => Carbon::now()->addMonths(36),
            ],
            [
                'reference' => 'PRD-008',
                'barcode' => '61811001008',
                'name' => 'Baguette Tradition Française',
                'category_id' => $categories['boulangerie-patisserie']->id,
                'brand' => 'Boulangerie Maison',
                'unit' => 'pcs',
                'buy_price' => 150,
                'sell_price' => 250,
                'stock_quantity' => 50,
                'alert_threshold' => 10,
                'supplier_id' => null,
                'expiration_date' => Carbon::now()->addDay(),
            ],
        ];

        $createdProducts = [];
        foreach ($productsData as $pData) {
            $prod = Product::create($pData);
            $createdProducts[$prod->reference] = $prod;

            // Mouvement initial de stock
            StockMovement::create([
                'product_id' => $prod->id,
                'user_id' => $admin->id,
                'type' => 'in',
                'quantity' => $prod->stock_quantity,
                'previous_stock' => 0,
                'new_stock' => $prod->stock_quantity,
                'reason' => 'Stock initial au lancement',
                'reference' => 'INIT-'.$prod->reference,
            ]);
        }

        // 6. Clients
        $client1 = Customer::create([
            'name' => 'Kouassi',
            'firstname' => 'Jean-Marc',
            'phone' => '+225 07 48 99 22 11',
            'email' => 'jm.kouassi@gmail.com',
            'address' => 'Cocody Angré 7e Tranche, Villa 42',
            'debt_balance' => 0,
        ]);

        $client2 = Customer::create([
            'name' => 'Diallo',
            'firstname' => 'Fatoumata',
            'phone' => '+225 05 55 44 33 22',
            'email' => 'fatou.diallo@yahoo.fr',
            'address' => 'Riviera Palmeraie, Rue Ministre',
            'debt_balance' => 15000,
        ]);

        $client3 = Customer::create([
            'name' => 'N\'Guessan',
            'firstname' => 'Clarisse',
            'phone' => '+225 01 02 44 88 99',
            'email' => 'clarisse.nguessan@gmail.com',
            'address' => 'Marcory Zone 4, Rue du 7 Décembre',
            'debt_balance' => 0,
        ]);

        // 7. Caisses
        $caisse1 = CashRegister::create([
            'name' => 'Caisse Principale N°1 (Allée A)',
            'code' => 'CAISSE-01',
            'status' => 'open',
        ]);

        $caisse2 = CashRegister::create([
            'name' => 'Caisse Express N°2 (Allée B)',
            'code' => 'CAISSE-02',
            'status' => 'closed',
        ]);

        // 8. Session de caisse active
        $sessionCaisse = CashSession::create([
            'cash_register_id' => $caisse1->id,
            'user_id' => $caissier->id,
            'opening_amount' => 50000,
            'closing_amount_theory' => 50000,
            'status' => 'open',
            'opened_at' => Carbon::now()->setTime(8, 0, 0),
            'notes' => 'Prise de poste matinale normale',
        ]);

        // Mouvement ouverture fond de caisse
        CashMovement::create([
            'cash_session_id' => $sessionCaisse->id,
            'user_id' => $caissier->id,
            'type' => 'in',
            'amount' => 50000,
            'reason' => 'Fond de caisse initial',
        ]);

        // 9. Ventes d'exemples
        $sale1 = Sale::create([
            'sale_number' => 'VNT-'.date('Ymd').'-0001',
            'user_id' => $caissier->id,
            'cash_session_id' => $sessionCaisse->id,
            'customer_id' => $client1->id,
            'subtotal' => 9000,
            'discount' => 500,
            'tax_amount' => 0,
            'total_amount' => 8500,
            'payment_method' => 'cash',
            'amount_received' => 10000,
            'amount_change' => 1500,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $createdProducts['PRD-003']->id,
            'product_name' => $createdProducts['PRD-003']->name,
            'quantity' => 1,
            'unit_price' => 5500,
            'discount' => 500,
            'total_price' => 5000,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $createdProducts['PRD-001']->id,
            'product_name' => $createdProducts['PRD-001']->name,
            'quantity' => 4,
            'unit_price' => 500,
            'discount' => 0,
            'total_price' => 2000,
        ]);

        SaleItem::create([
            'sale_id' => $sale1->id,
            'product_id' => $createdProducts['PRD-004']->id,
            'product_name' => $createdProducts['PRD-004']->name,
            'quantity' => 1,
            'unit_price' => 1400,
            'discount' => 0,
            'total_price' => 1400,
        ]);

        // Vente avec livraison
        $sale2 = Sale::create([
            'sale_number' => 'VNT-'.date('Ymd').'-0002',
            'user_id' => $caissier->id,
            'cash_session_id' => $sessionCaisse->id,
            'customer_id' => $client2->id,
            'subtotal' => 25500,
            'discount' => 0,
            'tax_amount' => 0,
            'total_amount' => 25500,
            'payment_method' => 'mobile_money',
            'amount_received' => 25500,
            'amount_change' => 0,
            'status' => 'completed',
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $createdProducts['PRD-003']->id,
            'product_name' => $createdProducts['PRD-003']->name,
            'quantity' => 4,
            'unit_price' => 5500,
            'discount' => 0,
            'total_price' => 22000,
        ]);

        SaleItem::create([
            'sale_id' => $sale2->id,
            'product_id' => $createdProducts['PRD-006']->id,
            'product_name' => $createdProducts['PRD-006']->name,
            'quantity' => 7,
            'unit_price' => 500,
            'discount' => 0,
            'total_price' => 3500,
        ]);

        // Mise à jour de la session de caisse
        $sessionCaisse->update([
            'closing_amount_theory' => 50000 + 8500, // En espèces uniquement
        ]);

        CashMovement::create([
            'cash_session_id' => $sessionCaisse->id,
            'user_id' => $caissier->id,
            'type' => 'sale',
            'amount' => 8500,
            'reason' => 'Encaissement vente VNT-'.date('Ymd').'-0001',
        ]);

        // 10. Livraison assignée au livreur
        Delivery::create([
            'delivery_number' => 'LIV-'.date('Ymd').'-0001',
            'sale_id' => $sale2->id,
            'customer_id' => $client2->id,
            'livreur_id' => $livreur->id,
            'recipient_name' => 'Fatoumata Diallo',
            'recipient_phone' => '+225 05 55 44 33 22',
            'delivery_address' => 'Riviera Palmeraie, Rue Ministre, Villa 12B Abidjan',
            'total_amount' => 25500,
            'status' => 'in_transit', // en cours de livraison
            'otp_code' => '483921',
            'assigned_at' => Carbon::now()->subMinutes(30),
            'notes' => 'Appeler dès arrivée devant le portail vert.',
        ]);

        // Deuxième livraison en attente
        Delivery::create([
            'delivery_number' => 'LIV-'.date('Ymd').'-0002',
            'sale_id' => null,
            'customer_id' => $client3->id,
            'livreur_id' => $livreur->id,
            'recipient_name' => 'Clarisse N\'Guessan',
            'recipient_phone' => '+225 01 02 44 88 99',
            'delivery_address' => 'Marcory Zone 4, Rue du 7 Décembre, Immeuble Prestige Apt 301',
            'total_amount' => 18400,
            'status' => 'assigned',
            'otp_code' => '159753',
            'assigned_at' => Carbon::now()->subMinutes(10),
            'notes' => 'Sonner au 3e étage.',
        ]);

        // 11. Commande Fournisseur / Approvisionnement
        $purchase = Purchase::create([
            'reference' => 'CMD-FOURN-'.date('Ymd').'-001',
            'supplier_id' => $supplier1->id,
            'user_id' => $admin->id,
            'total_amount' => 150000,
            'status' => 'ordered',
            'order_date' => Carbon::now()->subDays(2),
            'notes' => 'Réapprovisionnement d\'urgence boissons sodas et eaux minérales',
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $createdProducts['PRD-001']->id,
            'quantity_ordered' => 200,
            'quantity_received' => 0,
            'unit_buy_price' => 350,
            'total_price' => 70000,
        ]);

        PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $createdProducts['PRD-002']->id,
            'quantity_ordered' => 320,
            'quantity_received' => 0,
            'unit_buy_price' => 250,
            'total_price' => 80000,
        ]);

        // 12. Dépenses d'exemple
        Expense::create([
            'title' => 'Facture électricité CIE Magasin',
            'category' => 'électricité',
            'amount' => 75000,
            'user_id' => $admin->id,
            'expense_date' => Carbon::now()->subDays(3),
            'notes' => 'Facture mensuelle d\'électricité des chambres froides et climatisation',
        ]);

        Expense::create([
            'title' => 'Carburant pour triporteur de livraison',
            'category' => 'transport',
            'amount' => 15000,
            'user_id' => $admin->id,
            'expense_date' => Carbon::now()->subDay(),
            'notes' => 'Ravitaillement carburant livreur',
        ]);

        // 13. Audit logs initiaux
        ActivityLog::create([
            'user_id' => $admin->id,
            'user_name' => $admin->full_name,
            'user_role' => 'admin',
            'action' => 'initialisation_systeme',
            'description' => 'Initialisation réussie de la plateforme GestMagasin avec les 4 profils utilisateurs et données de démonstration.',
            'ip_address' => '127.0.0.1',
        ]);
    }
}
