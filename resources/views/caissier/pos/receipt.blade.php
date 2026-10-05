<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Caisse #{{ $sale->invoice_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 76mm;
            margin: 0 auto;
            padding: 8px 4px;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-divider {
            border-top: 1px double #000;
            margin: 6px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 2px 0;
            font-size: 11px;
        }
        .actions {
            margin-top: 15px;
            text-align: center;
        }
        .btn {
            background-color: #0d9488;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
            font-family: sans-serif;
            font-weight: bold;
            margin: 4px;
            cursor: pointer;
            border: none;
        }
        .btn-secondary {
            background-color: #475569;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print actions">
        <button onclick="window.print()" class="btn">🖨️ Imprimer</button>
        <a href="{{ route('caissier.pos.index') }}" class="btn btn-secondary">🛒 Nouvelle Vente</a>
    </div>

    <div class="text-center">
        <h2 style="margin: 0; font-size: 16px; font-weight: bold;">{{ \App\Models\Setting::get('store_name', 'GESTMAGASIN SUPERMARCHÉ') }}</h2>
        <div>{{ \App\Models\Setting::get('store_address', 'Abidjan, Côte d\'Ivoire') }}</div>
        <div>Tél : {{ \App\Models\Setting::get('store_phone', '+225 07 00 00 00 00') }}</div>
        @if(\App\Models\Setting::get('store_tax_number'))
            <div>N° CC / RCCM : {{ \App\Models\Setting::get('store_tax_number') }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <div>
        <div><strong>Ticket :</strong> {{ $sale->sale_number ?? $sale->invoice_number }}</div>
        <div><strong>Date :</strong> {{ $sale->created_at->format('d/m/Y H:i:s') }}</div>
        <div><strong>Caissier :</strong> {{ $sale->user->full_name ?? $sale->user->name ?? 'Caissier' }}</div>
        @if($sale->customer)
            <div><strong>Client :</strong> {{ $sale->customer->full_name ?? $sale->customer->name }} {{ $sale->customer->phone ? '('.$sale->customer->phone.')' : '' }}</div>
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 50%;">Article</th>
                <th class="text-center" style="width: 15%;">Qté</th>
                <th class="text-right" style="width: 35%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->items as $item)
                <tr>
                    <td class="text-left">{{ $item->product_name }}</td>
                    <td class="text-center">{{ (float) $item->quantity }}</td>
                    <td class="text-right">{{ number_format($item->total_price ?? $item->subtotal, 0, ',', ' ') }}</td>
                </tr>
                <tr>
                    <td colspan="3" style="font-size: 9px; color: #444;">
                        @ {{ number_format($item->unit_price, 0, ',', ' ') }} FCFA {{ $item->discount > 0 ? '(Rem. '.number_format($item->discount, 0, ',', ' ').')' : '' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table>
        <tr>
            <td class="text-left">Total Brut :</td>
            <td class="text-right">{{ number_format($sale->subtotal, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if($sale->discount > 0)
            <tr>
                <td class="text-left">Remise :</td>
                <td class="text-right">-{{ number_format($sale->discount, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif
        @if(($sale->tax_amount ?? $sale->tax) > 0)
            <tr>
                <td class="text-left">TVA :</td>
                <td class="text-right">{{ number_format($sale->tax_amount ?? $sale->tax, 0, ',', ' ') }} FCFA</td>
            </tr>
        @endif
        <tr class="font-bold" style="font-size: 14px;">
            <td class="text-left" style="padding-top: 4px;">NET À PAYER :</td>
            <td class="text-right" style="padding-top: 4px;">{{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div>
        @if($sale->payment_method === 'credit')
            <div><strong>Mode :</strong> <span style="font-weight: bold;">À CRÉDIT (DETTE CLIENT)</span></div>
            <div><strong>Mis en compte client :</strong> {{ number_format($sale->total_amount, 0, ',', ' ') }} FCFA</div>
            @if($sale->customer)
                <div style="font-size: 11px; margin-top: 2px;"><strong>Solde dette actuelle client :</strong> {{ number_format($sale->customer->debt_balance, 0, ',', ' ') }} FCFA</div>
            @endif
        @else
            <div><strong>Mode de règlement :</strong> {{ strtoupper($sale->payment_method === 'cash' ? 'ESPÈCES' : ($sale->payment_method === 'mobile_money' ? 'MOBILE MONEY' : ($sale->payment_method === 'card' ? 'CARTE BANCAIRE' : $sale->payment_method))) }}</div>
            @if($sale->credit_amount > 0)
                <div style="color: #b45309;"><strong>Crédit accordé (dette) :</strong> {{ number_format($sale->credit_amount, 0, ',', ' ') }} FCFA</div>
            @endif
            @if(($sale->amount_received ?? $sale->received_amount) > 0)
                <div><strong>Montant Reçu :</strong> {{ number_format($sale->amount_received ?? $sale->received_amount, 0, ',', ' ') }} FCFA</div>
                <div class="font-bold"><strong>Monnaie Rendue :</strong> {{ number_format($sale->amount_change ?? $sale->change_amount, 0, ',', ' ') }} FCFA</div>
            @endif
        @endif
    </div>

    <div class="double-divider"></div>

    <div class="text-center" style="font-size: 10px; margin-top: 8px;">
        <p>{{ \App\Models\Setting::get('receipt_footer', 'Merci de votre visite et à très bientôt ! Les articles vendus ne sont ni repris ni échangés sans ticket.') }}</p>
        <div style="font-family: monospace; font-size: 10px; letter-spacing: 2px; margin-top: 6px;">
            *{{ $sale->sale_number ?? $sale->invoice_number }}*
        </div>
    </div>
</body>
</html>
