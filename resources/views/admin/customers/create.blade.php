@extends('layouts.app')

@section('title', 'Nouveau Client')
@section('page-title', 'Ajouter un Client')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between pb-6 mb-6 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800">Fiche d'enregistrement client</h3>
                <p class="text-xs text-slate-500">Pour le suivi des ventes, livraisons et crédits</p>
            </div>
            <a href="{{ route('admin.customers.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                <i class="fa-solid fa-arrow-left"></i> Retour
            </a>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Nom de famille <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Ex: Kouassi"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Prénom(s)</label>
                    <input type="text" name="firstname" value="{{ old('firstname') }}" placeholder="Ex: Jean-Marc"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Numéro de téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+225 07..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Adresse Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="client@domaine.com"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Adresse de livraison</label>
                    <textarea name="address" rows="2" placeholder="Commune, quartier, indications de repère..."
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">{{ old('address') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Solde initial de dette (si existant)</label>
                    <input type="number" step="0.01" name="debt_balance" value="{{ old('debt_balance', 0) }}"
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:bg-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                <a href="{{ route('admin.customers.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-300 text-slate-600 hover:bg-slate-50 text-xs font-bold transition">
                    Annuler
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition">
                    Créer le client
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
