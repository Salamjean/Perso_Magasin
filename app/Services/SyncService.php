<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashRegister;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SyncService
{
    protected string $remoteConnection = 'mysql_remote';

    public function isDirectMasterMode(): bool
    {
        return config('database.default') === 'mysql';
    }

    /**
     * Test if the remote MySQL database is reachable.
     */
    public function testRemoteConnection(): array
    {
        if ($this->isDirectMasterMode()) {
            try {
                DB::connection()->getPdo();

                return [
                    'connected' => true,
                    'host' => 'Serveur Central (Production Directe)',
                    'database' => config('database.connections.mysql.database', 'magasin_db'),
                    'latency_ms' => 0.1,
                    'message' => 'Connecté directement à la base de données MySQL de production.',
                ];
            } catch (Exception $e) {
                return [
                    'connected' => false,
                    'host' => 'Serveur Central',
                    'database' => config('database.connections.mysql.database', 'magasin_db'),
                    'latency_ms' => null,
                    'message' => 'Erreur de connexion MySQL : '.$e->getMessage(),
                ];
            }
        }

        $startTime = microtime(true);
        $host = config("database.connections.{$this->remoteConnection}.host", '127.0.0.1');
        $port = (int) config("database.connections.{$this->remoteConnection}.port", 3306);
        $dbName = config("database.connections.{$this->remoteConnection}.database", 'gestmagasin');

        $timeout = (float) env('DB_REMOTE_TIMEOUT', 5.0);
        $socket = @fsockopen($host, $port, $errno, $errstr, $timeout > 0 ? $timeout : 5.0);
        if (! $socket) {
            return [
                'connected' => false,
                'host' => $host,
                'database' => $dbName,
                'latency_ms' => null,
                'message' => "Serveur MySQL distant non joignable ({$host}:{$port}). Mode Hors-Ligne actif.",
            ];
        }
        fclose($socket);

        try {
            DB::connection($this->remoteConnection)->getPdo();
            $ping = DB::connection($this->remoteConnection)->select('SELECT 1 as ping');
            $latency = round((microtime(true) - $startTime) * 1000, 2);

            return [
                'connected' => true,
                'host' => $host,
                'database' => $dbName,
                'latency_ms' => $latency,
                'message' => "Connexion réussie au serveur distant ({$host} / {$dbName}) en {$latency}ms.",
            ];
        } catch (Exception $e) {
            return [
                'connected' => false,
                'host' => $host,
                'database' => $dbName,
                'latency_ms' => null,
                'message' => 'Impossible de joindre la base MySQL distante : '.$e->getMessage(),
            ];
        }
    }

    /**
     * PULL: Télécharge les données maîtres du serveur distant vers la base locale (Mode Hors-Ligne).
     */
    public function pull(): array
    {
        if ($this->isDirectMasterMode()) {
            return [
                'success' => true,
                'action' => 'pull',
                'summary' => [],
                'duration_seconds' => 0,
                'message' => 'Vous êtes sur le serveur central de production (Web) : toutes les données sont enregistrées en direct dans MySQL.',
                'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
            ];
        }

        $test = $this->testRemoteConnection();
        if (! $test['connected']) {
            throw new Exception($test['message']);
        }

        $startTime = microtime(true);
        $summary = [
            'users' => 0,
            'categories' => 0,
            'products' => 0,
            'customers' => 0,
            'suppliers' => 0,
            'settings' => 0,
            'cash_registers' => 0,
            'purchases' => 0,
        ];

        DB::beginTransaction();

        try {
            // 1. Paramètres (Settings)
            if ($this->hasRemoteTable('settings')) {
                $remoteSettings = DB::connection($this->remoteConnection)->table('settings')->get();
                foreach ($remoteSettings as $item) {
                    Setting::updateOrCreate(
                        ['key' => $item->key],
                        [
                            'value' => $item->value,
                            'group' => $item->group ?? 'general',
                            'type' => $item->type ?? 'string',
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['settings']++;
                }
            }

            // 2. Rôles et Utilisateurs (Users)
            if ($this->hasRemoteTable('users')) {
                $remoteUsers = DB::connection($this->remoteConnection)->table('users')->get();
                foreach ($remoteUsers as $item) {
                    User::updateOrCreate(
                        ['email' => $item->email],
                        [
                            'name' => $item->name,
                            'firstname' => $item->firstname ?? null,
                            'phone' => $item->phone ?? null,
                            'role' => $item->role ?? 'caissier',
                            'status' => $item->status ?? 'active',
                            'password' => $item->password,
                            'photo' => $item->photo ?? null,
                            'address' => $item->address ?? null,
                            'created_at' => $item->created_at ?? now(),
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['users']++;
                }
            }

            // 3. Catégories
            if ($this->hasRemoteTable('categories')) {
                $remoteCategories = DB::connection($this->remoteConnection)->table('categories')->get();
                foreach ($remoteCategories as $item) {
                    Category::updateOrCreate(
                        ['name' => $item->name],
                        [
                            'slug' => $item->slug ?? str()->slug($item->name),
                            'description' => $item->description ?? null,
                            'color' => $item->color ?? '#0056a6',
                            'icon' => $item->icon ?? null,
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['categories']++;
                }
            }

            // 4. Fournisseurs (Suppliers)
            if ($this->hasRemoteTable('suppliers')) {
                $remoteSuppliers = DB::connection($this->remoteConnection)->table('suppliers')->get();
                foreach ($remoteSuppliers as $item) {
                    Supplier::updateOrCreate(
                        ['phone' => $item->phone, 'company_name' => $item->company_name],
                        [
                            'contact_name' => $item->contact_name ?? null,
                            'email' => $item->email ?? null,
                            'address' => $item->address ?? null,
                            'city' => $item->city ?? null,
                            'tax_number' => $item->tax_number ?? null,
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['suppliers']++;
                }
            }

            // 5. Clients (Customers)
            if ($this->hasRemoteTable('customers')) {
                $remoteCustomers = DB::connection($this->remoteConnection)->table('customers')->get();
                foreach ($remoteCustomers as $item) {
                    Customer::updateOrCreate(
                        ['phone' => $item->phone],
                        [
                            'name' => $item->name,
                            'firstname' => $item->firstname ?? null,
                            'email' => $item->email ?? null,
                            'address' => $item->address ?? null,
                            'city' => $item->city ?? null,
                            'debt_balance' => $item->debt_balance ?? 0,
                            'max_debt_limit' => $item->max_debt_limit ?? 0,
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['customers']++;
                }
            }

            // 6. Caisses Physiques (CashRegisters)
            if ($this->hasRemoteTable('cash_registers')) {
                $remoteRegisters = DB::connection($this->remoteConnection)->table('cash_registers')->get();
                foreach ($remoteRegisters as $item) {
                    CashRegister::updateOrCreate(
                        ['code' => $item->code ?? ('CAISSE-'.$item->id)],
                        [
                            'name' => $item->name,
                            'status' => $item->status ?? 'closed',
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['cash_registers']++;
                }
            }

            // 7. Produits & Stocks (Catalog Master)
            if ($this->hasRemoteTable('products')) {
                $remoteProducts = DB::connection($this->remoteConnection)->table('products')->get();
                foreach ($remoteProducts as $item) {
                    $localCatId = null;
                    if (! empty($item->category_id)) {
                        $remoteCatName = DB::connection($this->remoteConnection)->table('categories')->where('id', $item->category_id)->value('name');
                        if ($remoteCatName) {
                            $localCatId = Category::where('name', $remoteCatName)->value('id');
                        }
                    }

                    $lookup = [];
                    if (! empty($item->barcode)) {
                        $lookup['barcode'] = $item->barcode;
                    } elseif (! empty($item->reference)) {
                        $lookup['reference'] = $item->reference;
                    } else {
                        $lookup['name'] = $item->name;
                    }

                    $buyPrice = (float) ($item->buy_price ?? 0);
                    if ($buyPrice <= 0 && ! empty($item->purchase_price)) {
                        $buyPrice = (float) $item->purchase_price;
                    }

                    $sellPrice = (float) ($item->sell_price ?? 0);
                    if ($sellPrice <= 0 && ! empty($item->sale_price)) {
                        $sellPrice = (float) $item->sale_price;
                    }

                    Product::updateOrCreate(
                        $lookup,
                        [
                            'reference' => $item->reference ?? ('REF-'.uniqid()),
                            'name' => $item->name,
                            'barcode' => $item->barcode ?? null,
                            'category_id' => $localCatId,
                            'purchase_price' => $buyPrice,
                            'buy_price' => $buyPrice,
                            'sale_price' => $sellPrice,
                            'sell_price' => $sellPrice,
                            'stock_quantity' => $item->stock_quantity ?? 0,
                            'alert_threshold' => $item->alert_threshold ?? 5,
                            'unit' => $item->unit ?? 'pcs',
                            'description' => $item->description ?? null,
                            'image' => $item->image ?? null,
                            'is_active' => (bool) ($item->is_active ?? true),
                            'updated_at' => $item->updated_at ?? now(),
                        ]
                    );
                    $summary['products']++;
                }
            }

            DB::commit();

            $duration = round(microtime(true) - $startTime, 2);
            $this->recordSyncLog('pull', true, $summary, $duration);

            return [
                'success' => true,
                'action' => 'pull',
                'summary' => $summary,
                'duration_seconds' => $duration,
                'message' => 'Téléchargement (PULL) depuis le serveur distant effectué avec succès.',
                'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            $this->recordSyncLog('pull', false, ['error' => $e->getMessage()], 0);
            throw $e;
        }
    }

    /**
     * PUSH: Envoie les opérations créées/modifiées localement vers le serveur distant (Ventes, Stocks, Livraisons, Caisses).
     */
    public function push(): array
    {
        if ($this->isDirectMasterMode()) {
            return [
                'success' => true,
                'action' => 'push',
                'summary' => [],
                'duration_seconds' => 0,
                'message' => 'Vous êtes sur le serveur central de production (Web) : toutes les données sont enregistrées en direct dans MySQL.',
                'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
            ];
        }

        $test = $this->testRemoteConnection();
        if (! $test['connected']) {
            throw new Exception($test['message']);
        }

        $startTime = microtime(true);
        $summary = [
            'settings' => 0,
            'users' => 0,
            'categories' => 0,
            'suppliers' => 0,
            'customers' => 0,
            'products' => 0,
            'cash_registers' => 0,
            'cash_sessions' => 0,
            'cash_movements' => 0,
            'sales' => 0,
            'sale_items' => 0,
            'stock_movements' => 0,
            'deliveries' => 0,
            'delivery_items' => 0,
            'inventories' => 0,
            'activity_logs' => 0,
        ];

        DB::connection($this->remoteConnection)->beginTransaction();

        try {
            // 0a. Paramètres
            if ($this->hasRemoteTable('settings')) {
                foreach (Setting::all() as $stg) {
                    DB::connection($this->remoteConnection)->table('settings')->updateOrInsert(
                        ['key' => $stg->key],
                        [
                            'value' => $stg->value,
                            'group' => $stg->group ?? 'general',
                            'type' => $stg->type ?? 'string',
                            'synced' => true,
                            'created_at' => $stg->created_at,
                            'updated_at' => $stg->updated_at,
                        ]
                    );
                    $summary['settings']++;
                }
            }

            // 0b. Utilisateurs
            if ($this->hasRemoteTable('users')) {
                foreach (User::all() as $usr) {
                    DB::connection($this->remoteConnection)->table('users')->updateOrInsert(
                        ['email' => $usr->email],
                        [
                            'id' => $usr->id,
                            'name' => $usr->name,
                            'firstname' => $usr->firstname,
                            'phone' => $usr->phone,
                            'role' => $usr->role,
                            'status' => $usr->status ?? 'active',
                            'password' => $usr->password,
                            'photo' => $usr->photo,
                            'address' => $usr->address,
                            'synced' => true,
                            'created_at' => $usr->created_at,
                            'updated_at' => $usr->updated_at,
                        ]
                    );
                    $summary['users']++;
                }
            }

            // 0c. Catégories
            if ($this->hasRemoteTable('categories')) {
                foreach (Category::all() as $cat) {
                    DB::connection($this->remoteConnection)->table('categories')->updateOrInsert(
                        ['name' => $cat->name],
                        [
                            'id' => $cat->id,
                            'slug' => $cat->slug,
                            'description' => $cat->description,
                            'color' => $cat->color,
                            'icon' => $cat->icon,
                            'synced' => true,
                            'created_at' => $cat->created_at,
                            'updated_at' => $cat->updated_at,
                        ]
                    );
                    $summary['categories']++;
                }
            }

            // 0d. Fournisseurs
            if ($this->hasRemoteTable('suppliers')) {
                foreach (Supplier::all() as $sup) {
                    DB::connection($this->remoteConnection)->table('suppliers')->updateOrInsert(
                        ['phone' => $sup->phone, 'company_name' => $sup->company_name],
                        [
                            'id' => $sup->id,
                            'contact_name' => $sup->contact_name,
                            'email' => $sup->email,
                            'address' => $sup->address,
                            'city' => $sup->city,
                            'tax_number' => $sup->tax_number,
                            'synced' => true,
                            'created_at' => $sup->created_at,
                            'updated_at' => $sup->updated_at,
                        ]
                    );
                    $summary['suppliers']++;
                }
            }

            // 0e. Clients
            if ($this->hasRemoteTable('customers')) {
                foreach (Customer::all() as $cust) {
                    DB::connection($this->remoteConnection)->table('customers')->updateOrInsert(
                        ['phone' => $cust->phone],
                        [
                            'id' => $cust->id,
                            'name' => $cust->name,
                            'firstname' => $cust->firstname,
                            'email' => $cust->email,
                            'address' => $cust->address,
                            'city' => $cust->city,
                            'debt_balance' => $cust->debt_balance ?? 0,
                            'max_debt_limit' => $cust->max_debt_limit ?? 0,
                            'synced' => true,
                            'created_at' => $cust->created_at,
                            'updated_at' => $cust->updated_at,
                        ]
                    );
                    $summary['customers']++;
                }
            }

            // 0f. Caisses Physiques
            if ($this->hasRemoteTable('cash_registers')) {
                foreach (CashRegister::all() as $reg) {
                    DB::connection($this->remoteConnection)->table('cash_registers')->updateOrInsert(
                        ['code' => $reg->code],
                        [
                            'id' => $reg->id,
                            'name' => $reg->name,
                            'status' => $reg->status,
                            'synced' => true,
                            'created_at' => $reg->created_at,
                            'updated_at' => $reg->updated_at,
                        ]
                    );
                    $summary['cash_registers']++;
                }
            }

            // 0g. Produits & Stocks
            if ($this->hasRemoteTable('products')) {
                foreach (Product::all() as $prod) {
                    $lookup = [];
                    if (! empty($prod->barcode)) {
                        $lookup['barcode'] = $prod->barcode;
                    } elseif (! empty($prod->reference)) {
                        $lookup['reference'] = $prod->reference;
                    } else {
                        $lookup['name'] = $prod->name;
                    }

                    $buyPrice = (float) ($prod->buy_price ?? 0);
                    if ($buyPrice <= 0 && ! empty($prod->purchase_price)) {
                        $buyPrice = (float) $prod->purchase_price;
                    }

                    $sellPrice = (float) ($prod->sell_price ?? 0);
                    if ($sellPrice <= 0 && ! empty($prod->sale_price)) {
                        $sellPrice = (float) $prod->sale_price;
                    }

                    DB::connection($this->remoteConnection)->table('products')->updateOrInsert(
                        $lookup,
                        [
                            'id' => $prod->id,
                            'category_id' => $prod->category_id,
                            'supplier_id' => $prod->supplier_id,
                            'reference' => $prod->reference,
                            'name' => $prod->name,
                            'barcode' => $prod->barcode,
                            'purchase_price' => $buyPrice,
                            'buy_price' => $buyPrice,
                            'sale_price' => $sellPrice,
                            'sell_price' => $sellPrice,
                            'stock_quantity' => $prod->stock_quantity,
                            'alert_threshold' => $prod->alert_threshold ?? 5,
                            'unit' => $prod->unit,
                            'description' => $prod->description,
                            'image' => $prod->image,
                            'is_active' => $prod->is_active ? 1 : 0,
                            'synced' => true,
                            'created_at' => $prod->created_at,
                            'updated_at' => $prod->updated_at,
                        ]
                    );
                    $summary['products']++;
                }
            }

            // 1. Sessions de Caisse
            if ($this->hasRemoteTable('cash_sessions')) {
                $localSessions = CashSession::with(['user', 'cashRegister'])->get();
                foreach ($localSessions as $sess) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($sess->user?->email);
                    $remoteRegId = $this->getRemoteRegisterIdByCode($sess->cashRegister?->code);

                    DB::connection($this->remoteConnection)->table('cash_sessions')->updateOrInsert(
                        ['id' => $sess->id],
                        [
                            'cash_register_id' => $remoteRegId ?? $sess->cash_register_id,
                            'user_id' => $remoteUserId ?? $sess->user_id,
                            'opened_at' => $sess->opened_at,
                            'closed_at' => $sess->closed_at,
                            'opening_amount' => $sess->opening_amount,
                            'closing_amount_theory' => $sess->closing_amount_theory,
                            'closing_amount_real' => $sess->closing_amount_real,
                            'difference' => $sess->difference,
                            'status' => $sess->status,
                            'notes' => $sess->notes,
                            'synced' => true,
                            'created_at' => $sess->created_at,
                            'updated_at' => $sess->updated_at,
                        ]
                    );
                    $summary['cash_sessions']++;
                }
            }

            // 2. Mouvements de Caisse
            if ($this->hasRemoteTable('cash_movements')) {
                $localMovements = CashMovement::with('user')->get();
                foreach ($localMovements as $mov) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($mov->user?->email);

                    DB::connection($this->remoteConnection)->table('cash_movements')->updateOrInsert(
                        ['id' => $mov->id],
                        [
                            'cash_session_id' => $mov->cash_session_id,
                            'user_id' => $remoteUserId ?? $mov->user_id,
                            'type' => $mov->type,
                            'amount' => $mov->amount,
                            'reason' => $mov->reason,
                            'synced' => true,
                            'created_at' => $mov->created_at,
                            'updated_at' => $mov->updated_at,
                        ]
                    );
                    $summary['cash_movements']++;
                }
            }

            // 3. Ventes & Lignes de vente (Sale & SaleItems)
            if ($this->hasRemoteTable('sales')) {
                $localSales = Sale::with(['items', 'customer', 'user'])->get();
                foreach ($localSales as $sale) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($sale->user?->email);
                    $remoteCustId = $this->getRemoteCustomerIdByPhone($sale->customer?->phone);

                    DB::connection($this->remoteConnection)->table('sales')->updateOrInsert(
                        ['sale_number' => $sale->sale_number],
                        [
                            'id' => $sale->id,
                            'user_id' => $remoteUserId ?? $sale->user_id,
                            'cash_session_id' => $sale->cash_session_id,
                            'customer_id' => $remoteCustId ?? $sale->customer_id,
                            'subtotal' => $sale->subtotal,
                            'discount' => $sale->discount,
                            'tax_amount' => $sale->tax_amount,
                            'total_amount' => $sale->total_amount,
                            'payment_method' => $sale->payment_method,
                            'amount_received' => $sale->amount_received,
                            'amount_change' => $sale->amount_change,
                            'credit_amount' => $sale->credit_amount ?? 0,
                            'status' => $sale->status,
                            'cancellation_reason' => $sale->cancellation_reason,
                            'cancelled_by' => $sale->cancelled_by,
                            'synced' => true,
                            'created_at' => $sale->created_at,
                            'updated_at' => $sale->updated_at,
                        ]
                    );
                    $summary['sales']++;

                    if ($this->hasRemoteTable('sale_items')) {
                        foreach ($sale->items as $sItem) {
                            $remoteProdId = $this->getRemoteProductIdByBarcodeOrName($sItem->product?->barcode, $sItem->product_name);

                            DB::connection($this->remoteConnection)->table('sale_items')->updateOrInsert(
                                ['id' => $sItem->id],
                                [
                                    'sale_id' => $sale->id,
                                    'product_id' => $remoteProdId ?? $sItem->product_id,
                                    'product_name' => $sItem->product_name,
                                    'quantity' => $sItem->quantity,
                                    'unit_price' => $sItem->unit_price,
                                    'discount' => $sItem->discount ?? 0,
                                    'subtotal' => $sItem->subtotal ?? 0,
                                    'total_price' => $sItem->total_price,
                                    'synced' => true,
                                    'created_at' => $sItem->created_at,
                                    'updated_at' => $sItem->updated_at,
                                ]
                            );
                            $summary['sale_items']++;
                        }
                    }
                }
            }

            // 4. Mouvements de Stock
            if ($this->hasRemoteTable('stock_movements')) {
                $localStockMovs = StockMovement::with(['product', 'user'])->get();
                foreach ($localStockMovs as $sm) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($sm->user?->email);
                    $remoteProdId = $this->getRemoteProductIdByBarcodeOrName($sm->product?->barcode, $sm->product?->name);

                    DB::connection($this->remoteConnection)->table('stock_movements')->updateOrInsert(
                        ['id' => $sm->id],
                        [
                            'product_id' => $remoteProdId ?? $sm->product_id,
                            'user_id' => $remoteUserId ?? $sm->user_id,
                            'type' => $sm->type,
                            'quantity' => $sm->quantity,
                            'previous_stock' => $sm->previous_stock,
                            'new_stock' => $sm->new_stock,
                            'reason' => $sm->reason,
                            'reference' => $sm->reference,
                            'synced' => true,
                            'created_at' => $sm->created_at,
                            'updated_at' => $sm->updated_at,
                        ]
                    );
                    $summary['stock_movements']++;
                }
            }

            // 5. Livraisons & Colis (Deliveries & DeliveryItems)
            if ($this->hasRemoteTable('deliveries')) {
                $localDeliveries = Delivery::with(['items', 'livreur', 'customer'])->get();
                foreach ($localDeliveries as $deliv) {
                    $remoteLivreurId = $this->getRemoteUserIdByEmail($deliv->livreur?->email);
                    $remoteCustId = $this->getRemoteCustomerIdByPhone($deliv->customer?->phone);

                    DB::connection($this->remoteConnection)->table('deliveries')->updateOrInsert(
                        ['delivery_number' => $deliv->delivery_number],
                        [
                            'id' => $deliv->id,
                            'sale_id' => $deliv->sale_id,
                            'customer_id' => $remoteCustId ?? $deliv->customer_id,
                            'livreur_id' => $remoteLivreurId ?? $deliv->livreur_id,
                            'recipient_name' => $deliv->recipient_name,
                            'recipient_phone' => $deliv->recipient_phone,
                            'delivery_address' => $deliv->delivery_address,
                            'total_amount' => $deliv->total_amount,
                            'status' => $deliv->status,
                            'otp_code' => $deliv->otp_code,
                            'failure_reason' => $deliv->failure_reason,
                            'notes' => $deliv->notes,
                            'assigned_at' => $deliv->assigned_at,
                            'delivered_at' => $deliv->delivered_at,
                            'synced' => true,
                            'created_at' => $deliv->created_at,
                            'updated_at' => $deliv->updated_at,
                        ]
                    );
                    $summary['deliveries']++;

                    if ($this->hasRemoteTable('delivery_items')) {
                        foreach ($deliv->items as $dItem) {
                            $remoteProdId = $this->getRemoteProductIdByBarcodeOrName($dItem->product?->barcode, $dItem->product_name);

                            DB::connection($this->remoteConnection)->table('delivery_items')->updateOrInsert(
                                ['id' => $dItem->id],
                                [
                                    'delivery_id' => $deliv->id,
                                    'product_id' => $remoteProdId ?? $dItem->product_id,
                                    'product_name' => $dItem->product_name,
                                    'quantity' => $dItem->quantity,
                                    'unit_price' => $dItem->unit_price,
                                    'total_price' => $dItem->total_price,
                                    'synced' => true,
                                    'created_at' => $dItem->created_at,
                                    'updated_at' => $dItem->updated_at,
                                ]
                            );
                            $summary['delivery_items']++;
                        }
                    }
                }
            }

            // 6. Inventaires & Lignes d'inventaire
            if ($this->hasRemoteTable('inventories')) {
                $localInventories = Inventory::with(['items', 'user'])->get();
                foreach ($localInventories as $inv) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($inv->user?->email);

                    DB::connection($this->remoteConnection)->table('inventories')->updateOrInsert(
                        ['reference' => $inv->reference ?? ('INV-'.$inv->id)],
                        [
                            'id' => $inv->id,
                            'user_id' => $remoteUserId ?? $inv->user_id,
                            'type' => $inv->type ?? 'general',
                            'category_id' => $inv->category_id,
                            'status' => $inv->status ?? 'completed',
                            'notes' => $inv->notes,
                            'synced' => true,
                            'created_at' => $inv->created_at,
                            'updated_at' => $inv->updated_at,
                        ]
                    );
                    $summary['inventories']++;

                    if ($this->hasRemoteTable('inventory_items')) {
                        foreach ($inv->items as $invItem) {
                            $remoteProdId = $this->getRemoteProductIdByBarcodeOrName($invItem->product?->barcode, null);

                            DB::connection($this->remoteConnection)->table('inventory_items')->updateOrInsert(
                                ['id' => $invItem->id],
                                [
                                    'inventory_id' => $inv->id,
                                    'product_id' => $remoteProdId ?? $invItem->product_id,
                                    'system_stock' => $invItem->system_stock ?? 0,
                                    'physical_stock' => $invItem->physical_stock ?? 0,
                                    'difference' => $invItem->difference ?? 0,
                                    'reason' => $invItem->reason ?? null,
                                    'synced' => true,
                                    'created_at' => $invItem->created_at,
                                    'updated_at' => $invItem->updated_at,
                                ]
                            );
                        }
                    }
                }
            }

            // 7. Journal d'activités (ActivityLogs)
            if ($this->hasRemoteTable('activity_logs')) {
                $localLogs = ActivityLog::with('user')->latest()->take(100)->get();
                foreach ($localLogs as $log) {
                    $remoteUserId = $this->getRemoteUserIdByEmail($log->user?->email);

                    DB::connection($this->remoteConnection)->table('activity_logs')->updateOrInsert(
                        ['id' => $log->id],
                        [
                            'user_id' => $remoteUserId ?? $log->user_id,
                            'action' => $log->action,
                            'description' => $log->description,
                            'ip_address' => $log->ip_address,
                            'synced' => true,
                            'created_at' => $log->created_at,
                            'updated_at' => $log->updated_at,
                        ]
                    );
                    $summary['activity_logs']++;
                }
            }

            // Marquer toutes les données distantes comme synchronisées (synced = 1)
            $this->markAllRemoteAsSynced();

            DB::connection($this->remoteConnection)->commit();

            // Marquer toutes les entités locales poussées comme synchronisées
            $this->markAllLocalAsSynced();

            $duration = round(microtime(true) - $startTime, 2);
            $this->recordSyncLog('push', true, $summary, $duration);

            return [
                'success' => true,
                'action' => 'push',
                'summary' => $summary,
                'duration_seconds' => $duration,
                'message' => 'Téléversement (PUSH) des opérations locales vers le serveur distant effectué avec succès.',
                'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
            ];
        } catch (Exception $e) {
            DB::connection($this->remoteConnection)->rollBack();
            $this->recordSyncLog('push', false, ['error' => $e->getMessage()], 0);
            throw $e;
        }
    }

    /**
     * Synchronisation complète : PUSH des données locales offline puis PULL du catalogue mis à jour.
     */
    public function syncAll(): array
    {
        if ($this->isDirectMasterMode()) {
            return [
                'success' => true,
                'action' => 'sync_all',
                'push' => [],
                'pull' => [],
                'duration_seconds' => 0,
                'message' => 'Vous êtes sur le serveur central de production (Web) : toutes les données sont déjà en temps réel dans MySQL. La synchronisation est réservée aux caisses Desktop.',
                'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
            ];
        }

        $startTime = microtime(true);
        $pushResult = $this->push();
        $pullResult = $this->pull();
        $duration = round(microtime(true) - $startTime, 2);

        $report = [
            'success' => true,
            'action' => 'sync_all',
            'push' => $pushResult['summary'],
            'pull' => $pullResult['summary'],
            'duration_seconds' => $duration,
            'message' => "Synchronisation complète (PUSH & PULL) réussie en {$duration}s.",
            'synced_at' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        Cache::put('last_sync_info', $report, now()->addDays(30));

        ActivityLog::log(
            'sync_completed',
            "Synchronisation automatique bidirectionnelle avec mysql_remote exécutée avec succès en {$duration}s."
        );

        $this->markAllLocalAsSynced();

        return $report;
    }

    /**
     * Obtenir l'état de la synchronisation et le statut du mode hors-ligne.
     */
    public function getSyncStatus(): array
    {
        $test = $this->testRemoteConnection();
        $lastSync = Cache::get('last_sync_info');
        $unsyncedBreakdown = $this->getUnsyncedBreakdown();
        $unsyncedCount = array_sum(array_column($unsyncedBreakdown, 'count'));

        return [
            'is_online' => $test['connected'],
            'remote_info' => $test,
            'last_sync' => $lastSync,
            'unsynced_count' => $unsyncedCount,
            'unsynced_breakdown' => $unsyncedBreakdown,
            'local_counts' => [
                'products' => Product::count(),
                'sales' => Sale::count(),
                'deliveries' => Delivery::count(),
                'sessions' => CashSession::count(),
                'stock_movements' => StockMovement::count(),
                'customers' => Customer::count(),
            ],
        ];
    }

    /**
     * Marquer toutes les entités locales comme synchronisées après un PUSH réussi.
     */
    public function markAllLocalAsSynced(): void
    {
        $tables = [
            'sales', 'sale_items', 'cash_sessions', 'cash_movements',
            'stock_movements', 'deliveries', 'delivery_items',
            'inventories', 'inventory_items', 'activity_logs',
            'customers', 'products', 'categories', 'suppliers',
            'cash_registers', 'settings', 'expenses', 'purchases', 'purchase_items', 'users',
        ];

        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'synced')) {
                    DB::table($table)
                        ->where(function ($q) {
                            $q->where('synced', false)
                                ->orWhere('synced', 0)
                                ->orWhere('synced', '0')
                                ->orWhereNull('synced');
                        })
                        ->update(['synced' => true]);
                }
            } catch (Exception $e) {
                // Ignore any table read error
            }
        }
    }

    /**
     * Marquer toutes les entités distantes comme synchronisées (synced = 1) sur mysql_remote.
     */
    public function markAllRemoteAsSynced(): void
    {
        $tables = [
            'sales', 'sale_items', 'cash_sessions', 'cash_movements',
            'stock_movements', 'deliveries', 'delivery_items',
            'inventories', 'inventory_items', 'activity_logs',
            'customers', 'products', 'categories', 'suppliers',
            'cash_registers', 'settings', 'expenses', 'purchases', 'purchase_items', 'users',
        ];

        foreach ($tables as $table) {
            try {
                if ($this->hasRemoteTable($table) && Schema::connection($this->remoteConnection)->hasColumn($table, 'synced')) {
                    DB::connection($this->remoteConnection)->table($table)
                        ->where(function ($q) {
                            $q->where('synced', false)
                                ->orWhere('synced', 0)
                                ->orWhere('synced', '0')
                                ->orWhereNull('synced');
                        })
                        ->update(['synced' => true]);
                }
            } catch (Exception $e) {
                // Ignore
            }
        }
    }

    /**
     * Récupère le détail des données locales non encore synchronisées pour TOUTES les tables.
     */
    public function getUnsyncedBreakdown(): array
    {
        $tables = [
            'sales' => 'Ventes',
            'sale_items' => 'Articles Vendus',
            'cash_sessions' => 'Sessions Caisse',
            'cash_movements' => 'Mouvements Caisse',
            'stock_movements' => 'Mouvements Stock',
            'deliveries' => 'Courses Livreur',
            'delivery_items' => 'Articles Livrés',
            'inventories' => 'Inventaires',
            'inventory_items' => 'Lignes Inventaire',
            'customers' => 'Clients',
            'products' => 'Produits & Stocks',
            'expenses' => 'Dépenses',
            'purchases' => 'Achats',
            'purchase_items' => 'Lignes Achats',
            'categories' => 'Catégories',
            'suppliers' => 'Fournisseurs',
            'cash_registers' => 'Caisses',
            'settings' => 'Paramètres',
            'users' => 'Utilisateurs',
            'activity_logs' => 'Logs d\'Activité',
        ];

        $breakdown = [];
        foreach ($tables as $table => $label) {
            try {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'synced')) {
                    $count = DB::table($table)
                        ->where(function ($query) {
                            $query->where('synced', false)
                                ->orWhere('synced', 0)
                                ->orWhere('synced', '0')
                                ->orWhereNull('synced');
                        })
                        ->count();

                    if ($count > 0) {
                        $breakdown[$table] = [
                            'label' => $label,
                            'count' => $count,
                        ];
                    }
                }
            } catch (Exception $e) {
                // Ignore
            }
        }

        return $breakdown;
    }

    /**
     * Compte le nombre total d'enregistrements en attente de synchronisation.
     */
    public function getUnsyncedCount(): int
    {
        $breakdown = $this->getUnsyncedBreakdown();

        return (int) array_sum(array_column($breakdown, 'count'));
    }

    // --- Helpers de résolution d'IDs pour éviter les conflits ---

    protected function hasRemoteTable(string $table): bool
    {
        try {
            return Schema::connection($this->remoteConnection)->hasTable($table);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getRemoteUserIdByEmail(?string $email): ?int
    {
        if (! $email) {
            return null;
        }

        return DB::connection($this->remoteConnection)->table('users')->where('email', $email)->value('id');
    }

    protected function getRemoteRegisterIdByCode(?string $code): ?int
    {
        if (! $code) {
            return null;
        }

        return DB::connection($this->remoteConnection)->table('cash_registers')->where('code', $code)->value('id');
    }

    protected function getRemoteCustomerIdByPhone(?string $phone): ?int
    {
        if (! $phone) {
            return null;
        }

        return DB::connection($this->remoteConnection)->table('customers')->where('phone', $phone)->value('id');
    }

    protected function getRemoteProductIdByBarcodeOrName(?string $barcode, ?string $name): ?int
    {
        if ($barcode) {
            $id = DB::connection($this->remoteConnection)->table('products')->where('barcode', $barcode)->value('id');
            if ($id) {
                return $id;
            }
        }
        if ($name) {
            return DB::connection($this->remoteConnection)->table('products')->where('name', $name)->value('id');
        }

        return null;
    }

    protected function recordSyncLog(string $action, bool $success, array $summary, float $duration): void
    {
        $logData = [
            'action' => $action,
            'success' => $success,
            'summary' => $summary,
            'duration' => $duration,
            'timestamp' => now()->toISOString(),
        ];

        Cache::put('sync_last_'.$action, $logData, now()->addDays(30));
    }
}
