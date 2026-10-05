@extends('layouts.app')

@section('title', 'Catalogue Produits')
@section('page-title', 'Catalogue & Disponibilité Stock')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200/80 shadow-sm">
        <form method="GET" action="{{ route('magasinier.products.index') }}" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-3 text-slate-400 text-xs"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, code-barres, réf..."
                    class="pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500 w-64">
            </div>

            <select name="category_id" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white">
                <option value="">Tous les rayons</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition">
                Rechercher
            </button>
        </form>
    </div>

    <!-- PRODUCTS GRID -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($products as $prod)
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm hover:shadow-md transition flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">{{ $prod->reference }}</span>
                        @if($prod->isOutOfStock())
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">Rupture</span>
                        @elseif($prod->isLowStock())
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">Stock Faible</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">Disponible</span>
                        @endif
                    </div>

                    <h4 class="font-bold text-sm text-slate-900 leading-snug">{{ $prod->name }}</h4>
                    <p class="text-xs text-slate-500 mt-1">{{ $prod->category->name ?? 'Rayon standard' }}</p>

                    <div class="mt-4 p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500">Stock actuel :</span>
                        <strong class="text-base font-black {{ $prod->isOutOfStock() ? 'text-rose-600' : ($prod->isLowStock() ? 'text-amber-600' : 'text-emerald-700') }}">
                            {{ (float)$prod->stock_quantity }} {{ $prod->unit }}
                        </strong>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="font-mono text-[11px] text-slate-400">{{ $prod->barcode ?? 'Pas de code-barres' }}</span>
                    <a href="{{ route('magasinier.products.show', $prod) }}" class="text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1">
                        Fiche <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-4 bg-white rounded-2xl p-12 text-center text-slate-400 border border-slate-200/80">
                <i class="fa-solid fa-box-open text-3xl mb-2"></i>
                <p>Aucun produit trouvé.</p>
            </div>
        @endforelse
    </div>

    @if($products->hasPages())
        <div class="p-4 bg-white rounded-2xl border border-slate-200/80">
            {{ $products->links() }}
        </div>
    @endif

</div>
@endsection
