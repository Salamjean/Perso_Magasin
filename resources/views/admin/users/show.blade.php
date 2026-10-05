@extends('layouts.app')

@section('title', 'Détails Utilisateur')
@section('page_title', 'Fiche Utilisateur')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- USER SUMMARY CARD -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-slate-100 text-[#0056a6] border border-slate-200 flex items-center justify-center font-black text-xl shadow-xs overflow-hidden shrink-0">
                @if($user->photo)
                    <img src="{{ asset('storage/' . $user->photo) }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr($user->firstname ?? '', 0, 1)) }}
                @endif
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h3 class="text-xl font-bold text-slate-900">{{ $user->full_name }}</h3>
                    
                    @if($user->role === 'admin')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                            <i class="fa-solid fa-shield-halved text-[10px] text-[#0056a6]"></i> Administrateur
                        </span>
                    @elseif($user->role === 'magasinier')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            <i class="fa-solid fa-boxes-stacked text-[10px] text-amber-600"></i> Magasinier
                        </span>
                    @elseif($user->role === 'caissier')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-blue-50 text-[#0056a6] border border-blue-200">
                            <i class="fa-solid fa-cash-register text-[10px] text-[#0056a6]"></i> Caissier
                        </span>
                    @elseif($user->role === 'livreur')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                            <i class="fa-solid fa-motorcycle text-[10px] text-teal-600"></i> Livreur
                        </span>
                    @endif

                    @if($user->status === 'active')
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Actif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Bloqué
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-1.5 flex items-center gap-3">
                    <span><i class="fa-solid fa-envelope mr-1 text-slate-400"></i> {{ $user->email ?? 'Aucun email' }}</span>
                    <span>•</span>
                    <span><i class="fa-solid fa-phone mr-1 text-slate-400"></i> {{ $user->phone ?? 'Aucun numéro' }}</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="px-3.5 py-2 bg-[#0056a6] hover:bg-[#004485] text-white rounded-xl text-xs font-bold shadow-xs transition flex items-center gap-1.5">
                <i class="fa-solid fa-pen-to-square"></i> Modifier
            </a>
            
            @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    @if($user->status === 'active')
                        <button type="button" 
                            onclick="confirmAction(this.closest('form'), {
                                title: 'Bloquer cet utilisateur ?',
                                text: 'L\'employé {{ addslashes($user->full_name) }} ne pourra plus se connecter.',
                                confirmText: 'Oui, bloquer',
                                confirmColor: '#e11d48',
                                icon: 'warning'
                            })"
                            class="px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-user-slash"></i> Bloquer
                        </button>
                    @else
                        <button type="button" 
                            onclick="confirmAction(this.closest('form'), {
                                title: 'Débloquer cet utilisateur ?',
                                text: 'L\'employé {{ addslashes($user->full_name) }} aura de nouveau accès à son compte.',
                                confirmText: 'Oui, débloquer',
                                confirmColor: '#0056a6',
                                icon: 'question'
                            })"
                            class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                            <i class="fa-solid fa-user-check"></i> Débloquer
                        </button>
                    @endif
                </form>
            @endif

            <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                Retour
            </a>
        </div>
    </div>

    <!-- AUDIT ACTIVITY LOGS -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm p-6">
        <h4 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
            <i class="fa-solid fa-clock-rotate-left text-indigo-600"></i>
            <span>Historique des Actions de {{ $user->full_name }}</span>
        </h4>

        <div class="divide-y divide-slate-100 text-xs">
            @forelse($userLogs as $log)
                <div class="py-3 flex items-start justify-between gap-4">
                    <div>
                        <p class="font-bold text-slate-800">{{ $log->action }}</p>
                        <p class="text-slate-600 mt-0.5">{{ $log->description }}</p>
                    </div>
                    <span class="text-[10px] text-slate-400 shrink-0 font-medium">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400 py-6 text-center">Aucune action enregistrée pour cet utilisateur.</p>
            @endforelse
        </div>

        @if($userLogs->hasPages())
            <div class="pt-4 border-t border-slate-100">
                {{ $userLogs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
