<?php

namespace App\Http\Controllers\Caissier;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Setting;
use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();
        $activeSession = CashSession::where('user_id', $user->id)
            ->where('status', 'open')
            ->with('cashRegister')
            ->latest()
            ->first();

        if (! $activeSession) {
            return redirect()->route('caissier.session.open')->with('warning', 'Veuillez d\'abord ouvrir votre caisse pour accéder au point de vente (TPV).');
        }

        $categories = Category::where('is_active', true)->withCount('products')->get();
        $products = Product::where('is_active', true)->where('stock_quantity', '>', 0)->with('category')->get();
        $customers = Customer::orderBy('name')->get();

        return view('caissier.pos.index', compact('activeSession', 'categories', 'products', 'customers'));
    }

    public function searchProduct(Request $request): JsonResponse
    {
        $query = $request->input('q');
        $barcode = $request->input('barcode');

        $productsQuery = Product::where('is_active', true);

        if ($barcode) {
            $product = $productsQuery->where('barcode', $barcode)->first();
            if ($product) {
                return response()->json(['success' => true, 'product' => $product]);
            }

            return response()->json(['success' => false, 'message' => 'Produit non trouvé pour ce code-barres.']);
        }

        if ($query) {
            $products = $productsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('reference', 'like', "%{$query}%")
                    ->orWhere('barcode', 'like', "%{$query}%");
            })->limit(20)->get();

            return response()->json(['success' => true, 'products' => $products]);
        }

        return response()->json(['success' => true, 'products' => []]);
    }

    public function checkout(Request $request): JsonResponse
    {
        $user = auth()->user();
        $activeSession = CashSession::where('user_id', $user->id)->where('status', 'open')->latest()->first();

        if (! $activeSession) {
            return response()->json(['success' => false, 'message' => 'Aucune session de caisse active.'], 422);
        }

        $validated = $request->validate([
            'customer_id' => ['nullable', 'exists:customers,id'],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.id' => ['required', 'exists:products,id'],
            'cart.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'cart.*.price' => ['required', 'numeric', 'min:0'],
            'cart.*.discount' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,mobile_money,card,transfer,other,credit'],
            'amount_received' => ['nullable', 'numeric', 'min:0'],
            'credit_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        // Pour une vente à crédit, le client doit obligatoirement être sélectionné
        if (($validated['payment_method'] ?? '') === 'credit' && empty($validated['customer_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pour une vente à crédit, veuillez sélectionner un compte client.',
            ], 422);
        }

        // En mode crédit, credit_amount = total (sera recalculé plus bas)
        // et amount_received = 0
        if (($validated['payment_method'] ?? '') === 'credit') {
            $validated['amount_received'] = 0;
        }

        try {
            $lowStockAlerts = [];

            $sale = DB::transaction(function () use ($validated, $user, $activeSession, &$lowStockAlerts) {
                $subtotal = 0;
                $totalDiscount = (float) ($validated['discount'] ?? 0);

                // Vérification du stock disponible
                foreach ($validated['cart'] as $item) {
                    $product = Product::findOrFail($item['id']);
                    if ($product->stock_quantity < $item['quantity']) {
                        throw new \Exception("Stock insuffisant pour {$product->name} (Disponible: {$product->stock_quantity})");
                    }
                    $subtotal += ($item['price'] * $item['quantity']);
                }

                $totalAmount = max(0, $subtotal - $totalDiscount);
                $amountReceived = (float) ($validated['amount_received'] ?? 0);
                $amountChange = max(0, $amountReceived - $totalAmount);

                // Mode crédit total : credit_amount = totalAmount
                $isFullCredit = ($validated['payment_method'] === 'credit');
                $creditAmount = $isFullCredit
                    ? $totalAmount
                    : (float) ($validated['credit_amount'] ?? 0);

                $saleNumber = 'VNT-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

                $sale = Sale::create([
                    'sale_number' => $saleNumber,
                    'user_id' => $user->id,
                    'cash_session_id' => $activeSession->id,
                    'customer_id' => $validated['customer_id'] ?? null,
                    'subtotal' => $subtotal,
                    'discount' => $totalDiscount,
                    'tax_amount' => 0,
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'amount_received' => $amountReceived,
                    'amount_change' => $amountChange,
                    'credit_amount' => $creditAmount,
                    'status' => 'completed',
                ]);

                // Enregistrement des lignes et décrémentation des stocks
                foreach ($validated['cart'] as $item) {
                    $product = Product::findOrFail($item['id']);
                    $itemDiscount = (float) ($item['discount'] ?? 0);
                    $lineTotal = ($item['price'] * $item['quantity']) - $itemDiscount;

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'discount' => $itemDiscount,
                        'total_price' => $lineTotal,
                    ]);

                    $prevStock = (float) $product->stock_quantity;
                    $product->decrement('stock_quantity', $item['quantity']);
                    $product->refresh();

                    // Détection si le produit a atteint ou franchi son seuil d'alerte
                    if ($product->stock_quantity <= $product->alert_threshold) {
                        $lowStockAlerts[] = [
                            'id' => $product->id,
                            'name' => $product->name,
                            'remaining' => (float) $product->stock_quantity,
                            'threshold' => (float) $product->alert_threshold,
                            'unit' => $product->unit ?? 'U',
                        ];
                    }

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => $user->id,
                        'type' => 'sale',
                        'quantity' => $item['quantity'],
                        'previous_stock' => $prevStock,
                        'new_stock' => $product->stock_quantity,
                        'reason' => 'Vente en caisse #'.$sale->sale_number,
                        'reference' => $sale->sale_number,
                    ]);
                }

                // Mise à jour du théorique de caisse
                // ⚠️  Mode crédit total → rien en caisse
                // ⚠️  Mode espèces avec crédit partiel → on retire le crédit
                if (! $isFullCredit && $validated['payment_method'] === 'cash') {
                    $effectiveCashIn = max(0, $totalAmount - $creditAmount);

                    if ($effectiveCashIn > 0) {
                        $activeSession->increment('closing_amount_theory', $effectiveCashIn);

                        CashMovement::create([
                            'cash_session_id' => $activeSession->id,
                            'user_id' => $user->id,
                            'type' => 'sale',
                            'amount' => $effectiveCashIn,
                            'reason' => 'Encaissement vente '.$sale->sale_number
                                .($creditAmount > 0 ? " (crédit client : {$creditAmount} FCFA non encaissé)" : ''),
                        ]);
                    }
                }

                ActivityLog::log(
                    'vente_effectuee',
                    "Vente {$sale->sale_number} réalisée par {$user->full_name} d'un montant de {$sale->total_amount} FCFA ({$sale->payment_method})"
                );

                // Crédit accordé au client (mise en dette)
                if ($creditAmount > 0 && ! empty($validated['customer_id'])) {
                    $customer = Customer::find($validated['customer_id']);
                    if ($customer) {
                        $customer->increment('debt_balance', $creditAmount);

                        ActivityLog::log(
                            'credit_accorde',
                            "Crédit de {$creditAmount} FCFA accordé au client {$customer->full_name} pour la vente {$sale->sale_number}"
                        );
                    }
                }

                return $sale;
            });

            return response()->json([
                'success' => true,
                'message' => 'Vente enregistrée avec succès !',
                'sale_id' => $sale->id,
                'sale_number' => $sale->sale_number,
                'total_amount' => $sale->total_amount,
                'amount_received' => $sale->amount_received,
                'amount_change' => $sale->amount_change,
                'credit_amount' => (float) $sale->credit_amount,
                'low_stock_alerts' => $lowStockAlerts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['user', 'customer', 'items.product', 'cashSession.cashRegister']);
        $storeName = Setting::get('store_name', 'Supermarché GestMagasin');
        $storePhone = Setting::get('store_phone', '+225 27 22 00 00 00');
        $storeAddress = Setting::get('store_address', 'Abidjan');
        $receiptFooter = Setting::get('receipt_footer', 'Merci pour votre visite !');
        $currency = Setting::get('currency', 'FCFA');

        return view('caissier.pos.receipt', compact(
            'sale',
            'storeName',
            'storePhone',
            'storeAddress',
            'receiptFooter',
            'currency'
        ));
    }
}
