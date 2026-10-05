@extends('layouts.app')

@section('title', 'Gestion des Mouvements de Stock')
@section('page_title', 'Mouvements de Stock (Entrées & Sorties)')

@section('content')
<div class="space-y-6 w-full">

    <!-- KPI STATS CARDS DU STOCK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- TOTAL MOUVEMENTS -->
        <a href="{{ route('admin.stock.index') }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-[#0056a6]/40 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Mouvements</p>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($stats['total_movements'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-[#0056a6] transition">Flux tracés</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-blue-50 text-[#0056a6] flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
        </a>

        <!-- ENTRÉES CE MOIS -->
        <a href="{{ route('admin.stock.index', ['type' => 'in']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-emerald-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Entrées ce mois</p>
                <h3 class="text-2xl font-extrabold text-emerald-600 mt-1 font-mono">+{{ number_format($stats['entries_this_month'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-emerald-600 transition">Approvisionnements</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-arrow-down"></i>
            </div>
        </a>

        <!-- SORTIES / PERTES CE MOIS -->
        <a href="{{ route('admin.stock.index', ['type' => 'out']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-rose-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Sorties / Pertes ce mois</p>
                <h3 class="text-2xl font-extrabold text-rose-600 mt-1 font-mono">-{{ number_format($stats['exits_this_month'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-rose-600 transition">Casse, avarie, retrait</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-arrow-up"></i>
            </div>
        </a>

        <!-- VENTES CE MOIS -->
        <a href="{{ route('admin.stock.index', ['type' => 'sale']) }}" 
           class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs hover:border-sky-300 hover:shadow-sm transition flex items-center justify-between group">
            <div>
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ventes en caisse</p>
                <h3 class="text-2xl font-extrabold text-[#0056a6] mt-1 font-mono">-{{ number_format($stats['sales_this_month'], 0, ',', ' ') }}</h3>
                <span class="text-[10px] text-slate-400 font-medium group-hover:text-[#0056a6] transition">Articles vendus</span>
            </div>
            <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
        </a>

    </div>

    <!-- BARRE D'ACTIONS & FILTRES -->
    <div class="bg-white p-4 sm:p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col xl:flex-row items-stretch xl:items-center justify-between gap-4">
        
        <!-- FORMULAIRE DE RECHERCHE -->
        <form method="GET" action="{{ route('admin.stock.index') }}" class="flex flex-wrap items-center gap-2.5 flex-1">
            
            <div class="relative flex-1 min-w-[200px]">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" 
                    placeholder="Réf mouvement, motif, produit..."
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:bg-white focus:border-[#0056a6] focus:outline-none transition">
            </div>

            <div class="w-full sm:w-auto">
                <select name="type" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les types</option>
                    <option value="in" {{ request('type') == 'in' ? 'selected' : '' }}>🟢 Entrée de stock</option>
                    <option value="out" {{ request('type') == 'out' ? 'selected' : '' }}>🔴 Sortie / Perte</option>
                    <option value="sale" {{ request('type') == 'sale' ? 'selected' : '' }}>🛒 Vente caisse</option>
                    <option value="return" {{ request('type') == 'return' ? 'selected' : '' }}>↩️ Retour client</option>
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <select name="product_id" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
                    <option value="">Tous les produits</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto">
                <input type="date" name="date" value="{{ request('date') }}" 
                    class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:bg-white focus:border-[#0056a6] focus:outline-none transition cursor-pointer">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                    <i class="fa-solid fa-filter text-xs"></i>
                    <span>Filtrer</span>
                </button>
                @if(request()->hasAny(['search', 'type', 'product_id', 'date']))
                    <a href="{{ route('admin.stock.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition" title="Réinitialiser les filtres">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- BOUTONS D'ACTIONS : ENTRÉE ET SORTIE DE STOCK -->
        <div class="flex items-center gap-2.5 shrink-0">
            <a href="{{ route('admin.stock.entry') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Entrée de Stock</span>
            </a>

            <a href="{{ route('admin.stock.exit') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer">
                <i class="fa-solid fa-minus text-xs"></i>
                <span>Sortie / Perte</span>
            </a>
        </div>
    </div>

    <!-- TABLEAU DES MOUVEMENTS DE STOCK (CENTRÉ & POLI) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-3.5 px-5">Date & Heure</th>
                        <th class="py-3.5 px-5">Produit concerné</th>
                        <th class="py-3.5 px-5 text-center">Type</th>
                        <th class="py-3.5 px-5 text-right">Quantité</th>
                        <th class="py-3.5 px-5 text-center">Évolution Stock</th>
                        <th class="py-3.5 px-5">Motif & Référence</th>
                        <th class="py-3.5 px-5 text-center">Opérateur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($movements as $m)
                        <tr class="hover:bg-slate-50/70 transition">
                            
                            <!-- DATE -->
                            <td class="py-3.5 px-5 align-middle text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                {{ $m->created_at->format('d/m/Y') }}
                                <span class="block text-[10px] text-slate-400">{{ $m->created_at->format('H:i:s') }}</span>
                            </td>

                            <!-- PRODUIT -->
                            <td class="py-3.5 px-5 align-middle">
                                @if($m->product)
                                    <a href="{{ route('admin.products.show', $m->product) }}" class="font-bold text-slate-900 hover:text-[#0056a6] transition block">
                                        {{ $m->product->name }}
                                    </a>
                                    <span class="text-[10px] text-slate-400 font-mono">
                                        Réf: {{ $m->product->reference }} • Barcode: {{ $m->product->barcode ?? 'N/A' }}
                                    </span>
                                @else
                                    <span class="text-slate-400 italic">Article supprimé</span>
                                @endif
                            </td>

                            <!-- TYPE -->
                            <td class="py-3.5 px-5 text-center align-middle">
                                @if($m->type === 'in')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-arrow-down text-[9px]"></i> Entrée
                                    </span>
                                @elseif($m->type === 'out')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i class="fa-solid fa-arrow-up text-[9px]"></i> Sortie
                                    </span>
                                @elseif($m->type === 'sale')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                                        <i class="fa-solid fa-cart-shopping text-[9px]"></i> Vente
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ ucfirst($m->type) }}
                                    </span>
                                @endif
                            </td>

                            <!-- QUANTITÉ -->
                            <td class="py-3.5 px-5 text-right align-middle font-bold text-xs font-mono {{ $m->type === 'in' || $m->type === 'return' ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $m->type === 'in' || $m->type === 'return' ? '+' : '-' }}{{ (float)$m->quantity }} {{ $m->product->unit ?? 'u' }}
                            </td>

                            <!-- ÉVOLUTION -->
                            <td class="py-3.5 px-5 text-center align-middle font-mono text-[11px] text-slate-500 whitespace-nowrap">
                                {{ (float)$m->previous_stock }} ➔ <strong class="text-slate-900">{{ (float)$m->new_stock }}</strong>
                            </td>

                            <!-- MOTIF & RÉFÉRENCE -->
                            <td class="py-3.5 px-5 align-middle">
                                <p class="font-semibold text-slate-800">{{ $m->reason }}</p>
                                <p class="text-[10px] text-slate-400 font-mono">{{ $m->reference }}</p>
                            </td>

                            <!-- OPÉRATEUR -->
                            <td class="py-3.5 px-5 text-center align-middle text-slate-600 text-[11px]">
                                <span class="inline-flex items-center gap-1 text-slate-700 font-medium">
                                    <i class="fa-solid fa-user text-[10px] text-slate-400"></i>
                                    {{ $m->user->full_name ?? 'Système' }}
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-12 text-slate-400">
                                <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 text-lg">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Aucun mouvement de stock trouvé</p>
                                <p class="text-[11px] text-slate-400 mt-1">Enregistrez une entrée ou sortie pour approvisionner ou ajuster vos stocks.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
