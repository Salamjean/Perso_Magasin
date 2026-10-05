<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Delivery;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $query = Delivery::where('livreur_id', $user->id)->with(['customer', 'sale']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->latest()->paginate(15)->withQueryString();

        return view('livreur.deliveries.index', compact('deliveries'));
    }

    public function show(Delivery $delivery): View
    {
        if ($delivery->livreur_id !== auth()->id()) {
            abort(403, 'Cette livraison ne vous est pas assignée.');
        }

        $delivery->load(['customer', 'sale.items.product']);

        return view('livreur.deliveries.show', compact('delivery'));
    }

    public function start(Delivery $delivery): RedirectResponse
    {
        if ($delivery->livreur_id !== auth()->id()) {
            abort(403);
        }

        $delivery->update([
            'status' => 'in_transit',
        ]);

        ActivityLog::log(
            'livraison_en_cours',
            'Le livreur '.auth()->user()->full_name." a pris la route pour la livraison {$delivery->delivery_number}"
        );

        return back()->with('success', 'Livraison passée en cours de transport.');
    }

    public function validateDelivery(Request $request, Delivery $delivery): RedirectResponse
    {
        if ($delivery->livreur_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'otp_code' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        // Si un code OTP est renseigné et que la livraison en a un
        if (! empty($validated['otp_code']) && ! empty($delivery->otp_code) && trim($delivery->otp_code) !== trim($validated['otp_code'])) {
            return back()->withErrors(['otp_code' => 'Code OTP incorrect.']);
        }

        $delivery->update([
            'status' => 'delivered',
            'delivered_at' => Carbon::now(),
            'notes' => $validated['notes'] ?? $delivery->notes,
        ]);

        // Déduction de stock UNIQUEMENT pour les livraisons sans ticket de vente (sale_id === null)
        // Les livraisons sélectionnées avec ticket ont déjà eu leur stock déduit lors de la vente en caisse
        if (is_null($delivery->sale_id)) {
            $delivery->load('items.product');
            foreach ($delivery->items as $item) {
                if ($item->product_id && $item->product) {
                    $product = $item->product;
                    $prevStock = (float) $product->stock_quantity;
                    $product->decrement('stock_quantity', $item->quantity);
                    $product->refresh();

                    StockMovement::create([
                        'product_id' => $product->id,
                        'user_id' => auth()->id(),
                        'type' => 'sale',
                        'quantity' => $item->quantity,
                        'previous_stock' => $prevStock,
                        'new_stock' => $product->stock_quantity,
                        'reason' => 'Livraison directe hors vente confirmée #'.$delivery->delivery_number,
                        'reference' => $delivery->delivery_number,
                    ]);
                }
            }
        }

        ActivityLog::log(
            'livraison_effectuee',
            "Livraison {$delivery->delivery_number} validée avec succès par le livreur ".auth()->user()->full_name." auprès du client {$delivery->recipient_name}"
        );

        return redirect()->route('livreur.deliveries.index')->with('success', "Livraison {$delivery->delivery_number} confirmée avec succès !");
    }

    public function reportFailure(Request $request, Delivery $delivery): RedirectResponse
    {
        if ($delivery->livreur_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'failure_reason' => ['required', 'string', 'in:client_absent,adresse_incorrecte,client_injoignable,commande_refusee,autre'],
            'notes' => ['nullable', 'string'],
        ], [
            'failure_reason.required' => 'Veuillez préciser le motif de non-livraison.',
        ]);

        $reasons = [
            'client_absent' => 'Client absent au domicile',
            'adresse_incorrecte' => 'Adresse introuvable ou incorrecte',
            'client_injoignable' => 'Client injoignable par téléphone',
            'commande_refusee' => 'Commande refusée par le destinataire',
            'autre' => 'Autre problème',
        ];

        $reasonText = $reasons[$validated['failure_reason']] ?? $validated['failure_reason'];

        $delivery->update([
            'status' => 'failed',
            'failure_reason' => $reasonText.($request->filled('notes') ? ' - '.$request->notes : ''),
        ]);

        ActivityLog::log(
            'livraison_echec',
            "Échec de livraison pour {$delivery->delivery_number}. Motif : {$reasonText}"
        );

        return redirect()->route('livreur.deliveries.index')->with('warning', "Incident signalé pour la livraison {$delivery->delivery_number}.");
    }
}
