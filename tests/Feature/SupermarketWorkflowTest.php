<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\DeliveryItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupermarketWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_redirects_user_to_their_designated_dashboard(): void
    {
        // Admin via Email
        $response = $this->post(route('login.post'), [
            'login' => 'admin@gestmagasin.com',
            'password' => 'admin123',
        ]);
        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        // Magasinier via Téléphone
        $this->post(route('logout'));
        $response = $this->post(route('login.post'), [
            'login' => '+225 07 00 00 02',
            'password' => 'magasinier123',
        ]);
        $response->assertRedirect(route('magasinier.dashboard'));

        // Caissier via Téléphone
        $this->post(route('logout'));
        $response = $this->post(route('login.post'), [
            'login' => '+225 07 00 00 03',
            'password' => 'caissier123',
        ]);
        $response->assertRedirect(route('caissier.dashboard'));

        // Livreur via Téléphone
        $this->post(route('logout'));
        $response = $this->post(route('login.post'), [
            'login' => '+225 07 00 00 04',
            'password' => 'livreur123',
        ]);
        $response->assertRedirect(route('livreur.dashboard'));
    }

    public function test_role_middleware_restricts_unauthorized_access(): void
    {
        $caissier = User::where('role', 'caissier')->first();
        $this->actingAs($caissier);

        // Caissier tente d'accéder à l'espace Admin -> Redirection vers son propre dashboard
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('caissier.dashboard'));

        // Caissier tente d'accéder à l'espace Magasinier -> Redirection vers son propre dashboard
        $response = $this->get(route('magasinier.stock.index'));
        $response->assertRedirect(route('caissier.dashboard'));

        // Caissier accède à son propre espace -> 200 OK
        $response = $this->get(route('caissier.dashboard'));
        $response->assertStatus(200);
    }

    public function test_caissier_pos_sale_workflow(): void
    {
        $caissier = User::where('role', 'caissier')->first();
        $register = CashRegister::first();

        // Ouvrir une session de caisse
        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $caissier->id,
            'opening_amount' => 50000,
            'closing_amount_theory' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $this->actingAs($caissier);

        $product = Product::first();
        $initialStock = $product->stock_quantity;

        // Effectuer un encaissement au POS
        $response = $this->postJson(route('caissier.pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sell_price,
                    'quantity' => 2,
                    'discount' => 0,
                ],
            ],
            'payment_method' => 'cash',
            'amount_received' => $product->sell_price * 2 + 500,
            'discount' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Vérifier la décrémentation du stock
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_quantity' => $initialStock - 2,
        ]);
    }

    public function test_livreur_can_validate_delivery_with_otp(): void
    {
        $livreur = User::where('role', 'livreur')->first();
        $delivery = Delivery::where('livreur_id', $livreur->id)->first();

        $this->actingAs($livreur);

        // Échec avec un mauvais OTP
        $response = $this->post(route('livreur.deliveries.validate', $delivery->id), [
            'otp_code' => '000000',
        ]);
        $response->assertSessionHasErrors('otp_code');

        // Succès avec le bon OTP
        $response = $this->post(route('livreur.deliveries.validate', $delivery->id), [
            'otp_code' => $delivery->otp_code,
            'notes' => 'Remis en personne',
        ]);
        $response->assertRedirect(route('livreur.deliveries.index'));

        $this->assertDatabaseHas('deliveries', [
            'id' => $delivery->id,
            'status' => 'delivered',
        ]);
    }

    public function test_credit_sale_does_not_increment_cash_theory_and_updates_customer_debt(): void
    {
        $caissier = User::where('role', 'caissier')->first();
        $register = CashRegister::first();
        $customer = Customer::first();
        $initialDebt = (float) $customer->debt_balance;

        CashSession::where('user_id', $caissier->id)->where('status', 'open')->update(['status' => 'closed', 'closed_at' => now()]);

        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $caissier->id,
            'opening_amount' => 50000,
            'closing_amount_theory' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $this->actingAs($caissier);
        $product = Product::first();
        $totalSalePrice = (float) ($product->sell_price * 3);

        $response = $this->postJson(route('caissier.pos.checkout'), [
            'customer_id' => $customer->id,
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sell_price,
                    'quantity' => 3,
                    'discount' => 0,
                ],
            ],
            'payment_method' => 'credit',
            'amount_received' => 0,
            'discount' => 0,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Le montant théorique de caisse NE DOIT PAS avoir changé
        $session->refresh();
        $this->assertEquals(50000, (float) $session->closing_amount_theory);

        // La dette du client DOIT avoir augmenté du montant de la vente
        $customer->refresh();
        $this->assertEquals($initialDebt + $totalSalePrice, (float) $customer->debt_balance);
    }

    public function test_caissier_can_cancel_sale_directly_without_admin(): void
    {
        $caissier = User::where('role', 'caissier')->first();
        $register = CashRegister::first();
        $product = Product::first();
        $initialStock = (float) $product->stock_quantity;

        CashSession::where('user_id', $caissier->id)->where('status', 'open')->update(['status' => 'closed', 'closed_at' => now()]);

        $session = CashSession::create([
            'cash_register_id' => $register->id,
            'user_id' => $caissier->id,
            'opening_amount' => 50000,
            'closing_amount_theory' => 50000,
            'opened_at' => now(),
            'status' => 'open',
        ]);

        $this->actingAs($caissier);

        // 1. Effectuer une vente cash
        $response = $this->postJson(route('caissier.pos.checkout'), [
            'cart' => [
                [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->sell_price,
                    'quantity' => 2,
                    'discount' => 0,
                ],
            ],
            'payment_method' => 'cash',
            'amount_received' => $product->sell_price * 2,
            'discount' => 0,
        ]);

        $saleId = $response->json('sale_id');
        $sale = Sale::findOrFail($saleId);

        $session->refresh();
        $this->assertEquals(50000 + ($product->sell_price * 2), (float) $session->closing_amount_theory);

        // 2. Annuler la vente directement par le caissier
        $cancelResponse = $this->post(route('caissier.sales.cancel', $sale), [
            'cancellation_reason' => 'Erreur saisie caissier test',
        ]);

        $cancelResponse->assertRedirect();
        $cancelResponse->assertSessionHas('success');

        // 3. Vérifier le statut de la vente
        $sale->refresh();
        $this->assertEquals('cancelled', $sale->status);
        $this->assertEquals('Erreur saisie caissier test', $sale->cancellation_reason);
        $this->assertEquals($caissier->id, $sale->cancelled_by);

        // 4. Vérifier que les stocks ont été réinjectés
        $product->refresh();
        $this->assertEquals($initialStock, (float) $product->stock_quantity);

        // 5. Vérifier que la caisse a été décrémentée
        $session->refresh();
        $this->assertEquals(50000, (float) $session->closing_amount_theory);
    }

    public function test_caissier_can_schedule_delivery_for_purchased_sale(): void
    {
        $caissier = User::where('role', 'caissier')->first();
        $livreur = User::where('role', 'livreur')->first();
        $customer = Customer::first();
        $sale = Sale::first();

        $this->actingAs($caissier);

        $response = $this->post(route('caissier.deliveries.store'), [
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'recipient_name' => 'Client Test Livraison',
            'recipient_phone' => '+225 07 11 22 33 44',
            'delivery_address' => 'Cocody Angré 8ème Tranche',
            'total_amount' => 1500, // frais de livraison
            'livreur_id' => $livreur->id,
            'notes' => 'Livraison rapide demandée',
        ]);

        $response->assertRedirect(route('caissier.deliveries.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('deliveries', [
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'livreur_id' => $livreur->id,
            'recipient_name' => 'Client Test Livraison',
            'recipient_phone' => '+225 07 11 22 33 44',
            'status' => 'assigned',
        ]);
    }

    public function test_caissier_can_schedule_delivery_for_unsale_items(): void
    {
        $caissier = User::where('role', 'caissier')->first();

        $this->actingAs($caissier);

        $response = $this->post(route('caissier.deliveries.store'), [
            'sale_id' => null,
            'customer_id' => null,
            'recipient_name' => 'Destinataire Sans Achat',
            'recipient_phone' => '+225 05 99 88 77 66',
            'delivery_address' => 'Plateau Immeuble CCIA',
            'total_amount' => 5000,
            'livreur_id' => null,
            'notes' => 'Colis 3 cartons d\'échantillons promotionnels non facturés',
        ]);

        $response->assertRedirect(route('caissier.deliveries.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('deliveries', [
            'sale_id' => null,
            'recipient_name' => 'Destinataire Sans Achat',
            'status' => 'pending',
        ]);
    }

    public function test_delivery_confirmation_deducts_stock_for_unsale_items_delivery(): void
    {
        $livreur = User::where('role', 'livreur')->first();
        $product = Product::first();
        $initialStock = (float) $product->stock_quantity;

        // Créer une livraison sans vente (sale_id = null) avec 4 unités de produit
        $delivery = Delivery::create([
            'delivery_number' => 'LIV-TEST-001',
            'sale_id' => null,
            'livreur_id' => $livreur->id,
            'recipient_name' => 'Client Direct',
            'recipient_phone' => '+225 01 02 03 04 05',
            'delivery_address' => 'Marcory Zone 4',
            'total_amount' => 10000,
            'status' => 'in_transit',
            'otp_code' => '987654',
        ]);

        DeliveryItem::create([
            'delivery_id' => $delivery->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => 4,
            'unit_price' => $product->sell_price,
            'total_price' => $product->sell_price * 4,
        ]);

        // Le livreur valide la livraison avec OTP
        $this->actingAs($livreur);
        $response = $this->post(route('livreur.deliveries.validate', $delivery), [
            'otp_code' => '987654',
        ]);

        $response->assertRedirect(route('livreur.deliveries.index'));

        // Le stock DOIT avoir été déduit de 4 unités
        $product->refresh();
        $this->assertEquals($initialStock - 4, (float) $product->stock_quantity);

        // Un mouvement de stock doit avoir été enregistré
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'reference' => 'LIV-TEST-001',
            'quantity' => 4,
        ]);
    }

    public function test_delivery_confirmation_does_not_deduct_stock_again_for_ticket_sale_delivery(): void
    {
        $livreur = User::where('role', 'livreur')->first();
        $product = Product::first();
        $sale = Sale::first();
        $stockBeforeDelivery = (float) $product->stock_quantity;

        // Créer une livraison liée à une vente existante (sale_id !== null)
        $delivery = Delivery::create([
            'delivery_number' => 'LIV-TEST-SALE-001',
            'sale_id' => $sale->id,
            'livreur_id' => $livreur->id,
            'recipient_name' => 'Client Vente Ticket',
            'recipient_phone' => '+225 07 08 09 10 11',
            'delivery_address' => 'Cocody Riviera 2',
            'total_amount' => 0,
            'status' => 'in_transit',
            'otp_code' => '112233',
        ]);

        // Le livreur valide la livraison avec OTP
        $this->actingAs($livreur);
        $response = $this->post(route('livreur.deliveries.validate', $delivery), [
            'otp_code' => '112233',
        ]);

        $response->assertRedirect(route('livreur.deliveries.index'));

        // Le stock NE DOIT PAS être déduit à nouveau car déjà déduit au ticket
        $product->refresh();
        $this->assertEquals($stockBeforeDelivery, (float) $product->stock_quantity);
    }
}
