@extends('layouts.app')

@section('title', 'Point de Vente (TPV Caisse)')
@section('page_title', 'Caisse Tactile & Scan Code-barres')

@section('content')
<div class="h-[calc(100vh-140px)] flex flex-col lg:flex-row gap-5">

    <!-- GAUCHE: CATALOGUE, RECHERCHE & SCANNER CODE-BARRES -->
    <div class="flex-1 flex flex-col bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden min-w-0">

        <!-- BARRE DE SCAN & RECHERCHE AVANCÉE -->
        <div class="p-3.5 border-b border-slate-100 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 bg-slate-50/70">
            <!-- INPUT SCANNER CODE-BARRES PRINCIPAL -->
            <div class="relative flex-1">
                <div class="absolute left-3.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5 text-blue-600 pointer-events-none">
                    <i class="fa-solid fa-barcode text-base"></i>
                </div>
                <input type="text" id="barcode-input" autofocus 
                    placeholder="Scanner un code-barres (auto-ajout)..."
                    class="w-full pl-11 pr-24 py-2.5 bg-white border-2 border-blue-200 focus:border-[#0056a6] rounded-2xl text-xs font-mono font-bold focus:ring-4 focus:ring-blue-100 focus:outline-none shadow-xs transition"
                    autocomplete="off">
                <span id="scan-status-badge" class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 select-none transition-all duration-300">
                    <i class="fa-solid fa-bolt text-amber-500 mr-1"></i>Scan Actif
                </span>
            </div>

            <!-- RECHERCHE PAR NOM OU RÉFÉRENCE -->
            <div class="relative w-full sm:w-64">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" id="search-input" placeholder="Rechercher nom, réf..."
                    class="w-full pl-9 pr-8 py-2.5 bg-white border border-slate-300 rounded-2xl text-xs focus:ring-2 focus:ring-[#0056a6] focus:border-[#0056a6] focus:outline-none transition shadow-xs"
                    oninput="filterProductsByName(this.value)">
                <button type="button" onclick="clearSearch()" id="clear-search-btn" class="hidden absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-xmark text-xs"></i>
                </button>
            </div>
        </div>

        <!-- FILTRES RAYONS / CATÉGORIES -->
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center gap-2 overflow-x-auto shrink-0 bg-white no-scrollbar">
            <button onclick="filterCategory('all')" class="category-pill active px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap bg-[#0056a6] text-white shadow-xs">
                Tous les rayons ({{ $products->count() }})
            </button>
            @foreach($categories as $cat)
                <button onclick="filterCategory('{{ $cat->id }}')" class="category-pill px-3 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap bg-slate-100 hover:bg-slate-200 text-slate-700" data-cat="{{ $cat->id }}">
                    <span class="w-2 h-2 rounded-full inline-block mr-1.5" style="background-color: {{ $cat->color ?? '#0056a6' }}"></span>
                    {{ $cat->name }}
                </button>
            @endforeach
        </div>

        <!-- GRILLE DES PRODUITS AVEC IMAGES COMPACTES & INITIALES SUR FOND BLEU -->
        <div class="flex-1 p-3 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-5 gap-2.5 content-start" id="products-grid">
            @forelse($products as $p)
                @php
                    $imgUrl = $p->image ? asset('storage/' . $p->image) : null;
                    
                    // Calcul des initiales du produit
                    $words = preg_split("/\s+/", trim($p->name));
                    if (count($words) >= 2) {
                        $initials = mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
                    } else {
                        $initials = mb_strtoupper(mb_substr($p->name, 0, 2));
                    }
                @endphp
                <div class="product-card p-2 rounded-2xl border border-slate-200/80 bg-white hover:border-[#0056a6] hover:shadow-md hover:shadow-blue-900/10 transition duration-150 cursor-pointer flex flex-col justify-between select-none group relative"
                    data-id="{{ $p->id }}"
                    data-name="{{ $p->name }}"
                    data-price="{{ $p->sell_price }}"
                    data-barcode="{{ $p->barcode }}"
                    data-ref="{{ $p->reference }}"
                    data-cat="{{ $p->category_id }}"
                    data-stock="{{ $p->stock_quantity }}"
                    data-image="{{ $imgUrl ?? '' }}"
                    onclick="addToCart({{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->sell_price }}, {{ $p->stock_quantity }}, '{{ $imgUrl }}', {{ (float)($p->alert_threshold ?? 5) }})">

                    <!-- ZONE VISUELLE IMAGE OU INITIALES SUR FOND BLEU -->
                    <div class="w-full h-20 sm:h-22 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden mb-1.5 relative group-hover:scale-[1.01] transition">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $p->name }}" class="w-full h-full object-cover object-center" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <!-- FALLBACK SI ERREUR DE CHARGEMENT : INITIALES SUR FOND BLEU -->
                            <div class="w-full h-full hidden items-center justify-center text-white font-black font-mono text-lg tracking-wider select-none" style="background-color: #0056a6;">
                                {{ $initials }}
                            </div>
                        @else
                            <!-- INITIALES SUR FOND BLEU #0056a6 -->
                            <div class="w-full h-full flex items-center justify-center text-white font-black font-mono text-lg sm:text-xl tracking-wider select-none shadow-inner" style="background-color: #0056a6;">
                                {{ $initials }}
                            </div>
                        @endif

                        <!-- BADGE STOCK -->
                        <span class="absolute top-1 right-1 font-bold text-[9px] {{ $p->stock_quantity <= $p->alert_threshold ? 'text-amber-800 bg-amber-100/95 border-amber-200' : 'text-emerald-800 bg-emerald-100/95 border-emerald-200' }} px-1.5 py-0.2 rounded-md border shadow-xs">
                            {{ (float)$p->stock_quantity }} {{ $p->unit ?? 'U' }}
                        </span>

                        <!-- BADGE RAYON SI EXISTE -->
                        @if($p->category)
                            <span class="absolute bottom-1 left-1 text-[8px] font-bold text-white px-1.5 py-0.2 rounded shadow-xs truncate max-w-[85%]" style="background-color: {{ $p->category->color ?? '#0056a6' }}">
                                {{ $p->category->name }}
                            </span>
                        @endif
                    </div>

                    <!-- INFOS PRODUIT (PLUS COMPACT) -->
                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-[9px] text-slate-400 mb-0.5">
                                <span class="font-mono truncate">{{ $p->reference }}</span>
                                @if($p->barcode)
                                    <span class="font-mono truncate text-blue-600"><i class="fa-solid fa-barcode mr-0.5"></i>{{ substr($p->barcode, -4) }}</span>
                                @endif
                            </div>
                            <h4 class="font-bold text-[11px] text-slate-800 line-clamp-2 group-hover:text-[#0056a6] transition leading-tight" title="{{ $p->name }}">{{ $p->name }}</h4>
                        </div>

                        <!-- PRIX & BOUTON AJOUT -->
                        <div class="mt-2 flex items-center justify-between pt-1.5 border-t border-slate-100">
                            <span class="text-[11px] font-black text-slate-900">{{ number_format($p->sell_price, 0, ',', ' ') }} <span class="text-[9px] font-bold text-slate-500">FCFA</span></span>
                            <div class="w-5 h-5 rounded-lg bg-blue-50 text-[#0056a6] flex items-center justify-center text-[10px] group-hover:bg-[#0056a6] group-hover:text-white transition shadow-xs">
                                <i class="fa-solid fa-plus"></i>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full text-center py-16 text-slate-400">
                    <i class="fa-solid fa-box-open text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-600">Aucun produit disponible en stock</p>
                    <p class="text-xs text-slate-400 mt-1">Veuillez approvisionner des articles pour pouvoir les vendre.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- DROITE: PANIER D'ENCAISSEMENT & TOTAL -->
    <div class="w-full lg:w-[380px] bg-white rounded-3xl border border-slate-200/80 shadow-lg shadow-blue-900/5 flex flex-col overflow-hidden shrink-0">

        <!-- EN-TÊTE DU PANIER: CLIENT & ACTIONS -->
        <div class="p-3.5 border-b border-slate-100 bg-slate-50/80">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-cart-shopping text-sm text-[#0056a6]"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Ticket en cours</span>
                    <span id="cart-count-badge" class="px-1.5 py-0.2 rounded-full bg-blue-100 text-[#0056a6] text-[10px] font-black hidden">0</span>
                </div>
                <button onclick="clearCart()" class="text-[11px] font-bold text-rose-600 hover:text-rose-700 hover:underline flex items-center gap-1">
                    <i class="fa-solid fa-trash-can text-xs"></i> Vider
                </button>
            </div>

            <div class="relative">
                <select id="customer-select" class="w-full pl-3 pr-8 py-2 bg-white border border-slate-300 rounded-xl text-xs font-semibold text-slate-800 focus:ring-2 focus:ring-[#0056a6] focus:border-[#0056a6] appearance-none shadow-xs">
                    <option value="">Client Comptoir (Passage)</option>
                    @foreach($customers as $c)
                        <option value="{{ $c->id }}">{{ $c->full_name }} {{ $c->phone ? '('.$c->phone.')' : '' }}</option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] pointer-events-none"></i>
            </div>
        </div>

        <!-- LISTE DES ARTICLES DU PANIER (AVEC MINIATURES) -->
        <div class="flex-1 p-3 overflow-y-auto space-y-2" id="cart-items-container">
            <div id="empty-cart-msg" class="text-center py-16 text-slate-400">
                <div class="w-16 h-16 rounded-full bg-blue-50 text-[#0056a6] flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                    <i class="fa-solid fa-barcode"></i>
                </div>
                <p class="text-xs font-bold text-slate-600">Le panier est vide</p>
                <p class="text-[11px] text-slate-400 mt-1 max-w-[200px] mx-auto">Scannez un code-barres ou cliquez sur un article pour commencer.</p>
            </div>
        </div>

        <!-- RÉCAPITULATIF FINANCIER & BOUTON D'ENCAISSEMENT -->
        <div class="p-4 border-t border-slate-200 bg-slate-50 space-y-3">
            <div class="flex items-center justify-between text-xs text-slate-600">
                <span>Sous-total articles :</span>
                <span id="cart-subtotal" class="font-bold text-slate-900">0 FCFA</span>
            </div>

            <div class="pt-2 border-t border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 block">Total Net à Payer</span>
                    <span id="cart-total" class="text-2xl font-black text-[#0056a6]">0 FCFA</span>
                </div>
                <div class="text-right">
                    <span id="items-total-qty" class="text-xs font-bold text-slate-500">0 article(s)</span>
                </div>
            </div>

            <button onclick="openPaymentModal()" id="btn-pay" disabled
                class="w-full py-3.5 rounded-2xl bg-[#0056a6] hover:bg-[#004485] active:scale-[0.99] text-white font-black text-sm shadow-lg shadow-blue-900/20 transition duration-150 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2 cursor-pointer">
                <i class="fa-solid fa-cash-register text-base"></i>
                <span>ENCAISSER LA VENTE</span>
            </button>
        </div>
    </div>

</div>

<!-- MODAL ENCAISSEMENT & RÈGLEMENT -->
<div id="payment-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-xs hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-100 animate-fadeIn">
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-lg font-black text-slate-900">Règlement de la Vente</h3>
                <p class="text-xs text-slate-500">Choisissez le mode de règlement et saisissez le montant</p>
            </div>
            <button onclick="closePaymentModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-700 flex items-center justify-center transition">
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>

        <!-- TOTAL NET À PAYER -->
        <div class="p-4 rounded-2xl bg-blue-50/80 border border-blue-100 text-center mb-5">
            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">Montant Net à Percevoir</span>
            <h2 id="modal-net-total" class="text-3xl font-black text-[#0056a6] mt-0.5">0 FCFA</h2>
        </div>

        <form id="payment-form" onsubmit="submitSale(event)" class="space-y-4">
            <!-- SÉLECTION MODE DE PAIEMENT -->
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Mode de Paiement</label>
                <!-- 3 modes standards -->
                <div class="grid grid-cols-3 gap-2" id="payment-methods-standard">
                    <label class="p-3 rounded-2xl border-2 border-slate-200 text-center cursor-pointer hover:border-[#0056a6] transition has-[:checked]:border-[#0056a6] has-[:checked]:bg-blue-50/50">
                        <input type="radio" name="payment_method" value="cash" checked class="hidden" onchange="selectPaymentMode('cash')">
                        <i class="fa-solid fa-money-bill-wave text-[#0056a6] text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800">Espèces</span>
                    </label>
                    <label class="p-3 rounded-2xl border-2 border-slate-200 text-center cursor-pointer hover:border-[#0056a6] transition has-[:checked]:border-[#0056a6] has-[:checked]:bg-blue-50/50">
                        <input type="radio" name="payment_method" value="mobile_money" class="hidden" onchange="selectPaymentMode('mobile_money')">
                        <i class="fa-solid fa-mobile-screen-button text-amber-500 text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800">Mobile Money</span>
                    </label>
                    <label class="p-3 rounded-2xl border-2 border-slate-200 text-center cursor-pointer hover:border-[#0056a6] transition has-[:checked]:border-[#0056a6] has-[:checked]:bg-blue-50/50">
                        <input type="radio" name="payment_method" value="card" class="hidden" onchange="selectPaymentMode('card')">
                        <i class="fa-solid fa-credit-card text-indigo-600 text-lg block mb-1"></i>
                        <span class="text-xs font-bold text-slate-800">Carte Bancaire</span>
                    </label>
                </div>
                <!-- Bouton Crédit — visible seulement quand un client est sélectionné -->
                <div id="credit-method-wrapper" class="hidden mt-2">
                    <label class="p-3 rounded-2xl border-2 border-amber-300 text-center cursor-pointer hover:border-amber-500 transition has-[:checked]:border-amber-500 has-[:checked]:bg-amber-50 flex items-center justify-center gap-2 w-full">
                        <input type="radio" name="payment_method" value="credit" class="hidden" onchange="selectPaymentMode('credit')">
                        <i class="fa-solid fa-hand-holding-dollar text-amber-600 text-lg"></i>
                        <span class="text-xs font-bold text-amber-900">Tout mettre en Crédit (Dette client)</span>
                    </label>
                </div>
            </div>

            <!-- SECTION ESPÈCES / MONNAIE (masquée en mode crédit) -->
            <div id="cash-calc-section" class="space-y-3 pt-1">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Montant Reçu du Client (FCFA)</label>
                    <input type="number" step="100" id="amount-received" required
                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-lg font-black text-slate-900 focus:ring-2 focus:ring-[#0056a6] focus:border-[#0056a6] focus:outline-none"
                        oninput="calculateChange()">
                </div>
                <div class="grid grid-cols-4 gap-1.5">
                    <button type="button" onclick="setCashAmount(1000)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">1 000</button>
                    <button type="button" onclick="setCashAmount(2000)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">2 000</button>
                    <button type="button" onclick="setCashAmount(5000)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">5 000</button>
                    <button type="button" onclick="setCashAmount(10000)" class="py-1.5 bg-slate-100 hover:bg-slate-200 rounded-lg text-xs font-bold text-slate-700">10 000</button>
                </div>
                <div class="p-3.5 rounded-2xl bg-[#0056a6] text-white flex items-center justify-between shadow-inner">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-blue-200">Monnaie à Rendre</span>
                        <h3 id="change-display" class="text-2xl font-black text-white">0 FCFA</h3>
                    </div>
                    <i class="fa-solid fa-coins text-2xl text-blue-200"></i>
                </div>
            </div>

            <!-- INFO CRÉDIT TOTAL (visible seulement en mode crédit) -->
            <div id="credit-info-section" class="hidden">
                <div class="p-4 rounded-2xl bg-amber-50 border-2 border-amber-300 text-center space-y-2">
                    <i class="fa-solid fa-hand-holding-dollar text-3xl text-amber-500"></i>
                    <p class="text-xs font-bold text-amber-900">La totalité du montant sera enregistrée en dette</p>
                    <p id="credit-total-display" class="text-2xl font-black text-amber-800 font-mono">0 FCFA</p>
                    <p class="text-[10px] text-amber-700">Ce montant sera ajouté au solde de dette du client sélectionné. Aucune espèce n'entre en caisse.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2.5 rounded-xl border border-slate-300 text-slate-600 font-bold text-xs hover:bg-slate-50 transition">
                    Annuler
                </button>
                <button type="submit" id="submit-sale-btn" class="px-6 py-2.5 rounded-xl bg-[#0056a6] hover:bg-[#004485] text-white font-black text-xs shadow-md shadow-blue-900/20 transition cursor-pointer">
                    VALIDER & IMPRIMER LE TICKET
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Données locales des produits pour recherche et scan ultra-rapides sans latence
    const localProducts = @json($products);
    let cart = [];

    // Mini-toast léger (sans calque plein écran, aucun fond grisé)
    const Toast = {
        fire: ({ icon = 'success', title = '' } = {}) => {
            let stack = document.getElementById('mini-toast-stack');
            if (!stack) {
                stack = document.createElement('div');
                stack.id = 'mini-toast-stack';
                stack.style.cssText = 'position:fixed;top:14px;right:14px;z-index:9999;display:flex;flex-direction:column;gap:6px;pointer-events:none;';
                document.body.appendChild(stack);
            }

            const styles = {
                success: { color: '#059669', bg: '#ecfdf5', ico: 'fa-check' },
                error:   { color: '#e11d48', bg: '#fff1f2', ico: 'fa-xmark' },
                warning: { color: '#d97706', bg: '#fffbeb', ico: 'fa-exclamation' },
                info:    { color: '#0056a6', bg: '#eff6ff', ico: 'fa-info' },
            };
            const s = styles[icon] || styles.success;

            const el = document.createElement('div');
            el.style.cssText = 'display:flex;align-items:center;gap:7px;max-width:260px;padding:6px 10px;background:#fff;border:1px solid #e2e8f0;border-left:3px solid ' + s.color + ';border-radius:10px;box-shadow:0 4px 12px rgba(0,0,0,.10);font-size:11px;font-weight:700;color:#1e293b;opacity:0;transform:translateX(12px);transition:opacity .15s ease,transform .15s ease;';
            el.innerHTML = '<span style="width:18px;height:18px;min-width:18px;border-radius:6px;background:' + s.bg + ';color:' + s.color + ';display:flex;align-items:center;justify-content:center;font-size:9px;"><i class="fa-solid ' + s.ico + '"></i></span><span style="line-height:1.25;"></span>';
            el.lastChild.textContent = title;
            stack.appendChild(el);

            // Limiter à 3 notifications visibles
            while (stack.children.length > 3) {
                stack.firstChild.remove();
            }

            requestAnimationFrame(() => {
                el.style.opacity = '1';
                el.style.transform = 'translateX(0)';
            });

            setTimeout(() => {
                el.style.opacity = '0';
                el.style.transform = 'translateX(12px)';
                setTimeout(() => el.remove(), 180);
            }, icon === 'success' ? 1600 : 2800);
        }
    };

    // Bip sonore pour retour caisse professionnel
    function playBeep(success = true) {
        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;
            const ctx = new AudioContext();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            if (success) {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, ctx.currentTime); // Note A5 (bip net de caisse)
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.1);
                osc.start();
                osc.stop(ctx.currentTime + 0.1);
            } else {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(220, ctx.currentTime); // Son d'erreur grave
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
                osc.start();
                osc.stop(ctx.currentTime + 0.2);
            }
        } catch(e) {}
    }

    // Ajout au panier avec image et validation de stock + notification seuil
    function addToCart(productId, name, price, stockQty, imageUrl = null, alertThreshold = 5) {
        stockQty = parseFloat(stockQty) || 0;
        alertThreshold = parseFloat(alertThreshold) || 0;

        if (stockQty <= 0) {
            playBeep(false);
            Toast.fire({
                icon: 'error',
                title: `Rupture de stock : ${name} est épuisé (Stock: 0) !`
            });
            return;
        }

        const existing = cart.find(i => i.id === productId);
        let newQty = 1;

        if (existing) {
            if (existing.quantity >= stockQty) {
                playBeep(false);
                Toast.fire({
                    icon: 'warning',
                    title: `Stock maximal atteint (${stockQty} disponibles pour ${name})`
                });
                return;
            }
            existing.quantity += 1;
            newQty = existing.quantity;
        } else {
            cart.push({
                id: productId,
                name: name,
                price: parseFloat(price),
                quantity: 1,
                discount: 0,
                stock: stockQty,
                alert_threshold: alertThreshold,
                image: imageUrl
            });
        }

        playBeep(true);
        renderCart();

        Toast.fire({
            icon: 'success',
            title: `+1 ${name}`
        });
    }

    function changeQuantity(productId, delta) {
        const item = cart.find(i => i.id === productId);
        if (!item) return;

        if (delta > 0 && item.quantity >= item.stock) {
            playBeep(false);
            Toast.fire({
                icon: 'warning',
                title: `Stock disponible limité à ${item.stock} pour ${item.name}`
            });
            return;
        }

        item.quantity += delta;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id !== productId);
        } else {
            playBeep(true);
            const remaining = item.stock - item.quantity;
            if (delta > 0 && remaining <= (item.alert_threshold || 5) && remaining > 0) {
                Toast.fire({
                    icon: 'warning',
                    title: `⚠️ Seuil d'alerte pour ${item.name} : ${remaining} restant(s)`
                });
            }
        }
        renderCart();
    }

    function removeItem(productId) {
        cart = cart.filter(i => i.id !== productId);
        renderCart();
    }

    function clearCart() {
        cart = [];
        renderCart();
        refocusBarcodeInput();
    }

    // Helper pour extraire les initiales d'un produit (2 lettres)
    function getInitials(name) {
        if (!name) return 'PR';
        const words = name.trim().split(/\s+/).filter(w => w.length > 0);
        if (words.length >= 2) {
            return (words[0].substring(0, 1) + words[1].substring(0, 1)).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    // Affichage des éléments du panier avec miniature d'image ou initiales
    function renderCart() {
        const container = document.getElementById('cart-items-container');
        const btnPay = document.getElementById('btn-pay');
        const badge = document.getElementById('cart-count-badge');
        const qtyBadge = document.getElementById('items-total-qty');

        const totalItemsCount = cart.reduce((sum, item) => sum + item.quantity, 0);
        if (qtyBadge) {
            qtyBadge.innerText = `${totalItemsCount} article${totalItemsCount > 1 ? 's' : ''}`;
        }

        if (cart.length === 0) {
            container.innerHTML = `
                <div id="empty-cart-msg" class="text-center py-16 text-slate-400">
                    <div class="w-16 h-16 rounded-full bg-blue-50 text-[#0056a6] flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                        <i class="fa-solid fa-barcode"></i>
                    </div>
                    <p class="text-xs font-bold text-slate-600">Le panier est vide</p>
                    <p class="text-[11px] text-slate-400 mt-1 max-w-[200px] mx-auto">Scannez un code-barres ou cliquez sur un article pour commencer.</p>
                </div>
            `;
            btnPay.disabled = true;
            if (badge) badge.classList.add('hidden');
            updateCartTotals();
            return;
        }

        if (badge) {
            badge.innerText = cart.length;
            badge.classList.remove('hidden');
        }

        let html = '';
        cart.forEach(item => {
            const lineTotal = item.price * item.quantity;
            const initials = getInitials(item.name);
            const imgHtml = item.image 
                ? `<img src="${item.image}" alt="" class="w-full h-full object-cover" onerror="this.outerHTML='<div class=\\'w-full h-full flex items-center justify-center text-white font-black text-[10px] font-mono select-none\\' style=\\'background-color: #0056a6;\\'>${initials}</div>'">`
                : `<div class="w-full h-full flex items-center justify-center text-white font-black text-[10px] font-mono select-none" style="background-color: #0056a6;">${initials}</div>`;

            html += `
                <div class="p-2 bg-slate-50/90 hover:bg-slate-100/80 rounded-2xl border border-slate-200/80 flex items-center justify-between text-xs transition">
                    <div class="flex items-center gap-2.5 flex-1 min-w-0 pr-2">
                        <!-- MINIATURE PRODUIT OU INITIALES BLEUES DANS LE PANIER -->
                        <div class="w-8 h-8 rounded-xl overflow-hidden flex items-center justify-center shrink-0 shadow-xs border border-slate-200">
                            ${imgHtml}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h5 class="font-bold text-slate-900 truncate text-[11px]" title="${item.name}">${item.name}</h5>
                            <p class="text-[10px] text-slate-500 font-semibold">${item.price.toLocaleString('fr-FR')} FCFA</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <div class="flex items-center border border-slate-300 rounded-lg bg-white overflow-hidden shadow-xs">
                            <button type="button" onclick="changeQuantity(${item.id}, -1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-xs">-</button>
                            <span class="w-6 text-center font-bold text-slate-900 text-xs">${item.quantity}</span>
                            <button type="button" onclick="changeQuantity(${item.id}, 1)" class="w-6 h-6 flex items-center justify-center text-slate-600 hover:bg-slate-100 font-bold text-xs">+</button>
                        </div>

                        <span class="font-black text-slate-900 w-16 text-right text-xs">${lineTotal.toLocaleString('fr-FR')} F</span>

                        <button type="button" onclick="removeItem(${item.id})" class="w-6 h-6 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center transition" title="Supprimer">
                            <i class="fa-solid fa-xmark text-xs"></i>
                        </button>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        btnPay.disabled = false;
        updateCartTotals();
    }

    function updateCartTotals() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        document.getElementById('cart-subtotal').innerText = total.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('cart-total').innerText = total.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('modal-net-total').innerText = total.toLocaleString('fr-FR') + ' FCFA';
    }

    // Gestion du Scanner Code-barres (Ultra Réactif & Ajout Automatique)
    const barcodeInput = document.getElementById('barcode-input');

    function refocusBarcodeInput() {
        if (barcodeInput && document.activeElement !== barcodeInput) {
            const activeModal = document.getElementById('payment-modal');
            if (activeModal && activeModal.classList.contains('hidden')) {
                const isSearching = document.activeElement === document.getElementById('search-input');
                if (!isSearching) {
                    barcodeInput.focus();
                }
            }
        }
    }

    let lastScanTime = 0;
    let scanCooldownTimer = null;

    function setScanStatusBadge(state) {
        const badge = document.getElementById('scan-status-badge');
        if (!badge) return;

        if (state === 'cooldown') {
            badge.className = 'absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-200 select-none transition-all duration-200 shadow-xs';
            badge.innerHTML = '<i class="fa-solid fa-check text-emerald-500 mr-1 animate-pulse"></i>Ajouté (1s)';
        } else {
            badge.className = 'absolute right-3 top-1/2 -translate-y-1/2 text-[10px] font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 select-none transition-all duration-200 shadow-xs';
            badge.innerHTML = '<i class="fa-solid fa-bolt text-amber-500 mr-1"></i>Scan Actif';
        }
    }

    function processBarcode(code) {
        const barcode = code.trim();
        if (!barcode) return;

        const now = Date.now();
        // Délai de 1 seconde (1000 ms) entre chaque scan pour éviter les erreurs et doubles saisies
        if (now - lastScanTime < 1000) {
            barcodeInput.value = '';
            return;
        }

        lastScanTime = now;
        setScanStatusBadge('cooldown');
        if (scanCooldownTimer) clearTimeout(scanCooldownTimer);
        scanCooldownTimer = setTimeout(() => {
            setScanStatusBadge('ready');
        }, 1000);

        // 1. Chercher d'abord dans les données locales en mémoire (0 ms)
        const found = localProducts.find(p => p.barcode && String(p.barcode).trim().toLowerCase() === barcode.toLowerCase());
        
        if (found) {
            const imgUrl = found.image ? `{{ asset('storage') }}/${found.image}` : (found.image_url || null);
            addToCart(found.id, found.name, parseFloat(found.sell_price), parseFloat(found.stock_quantity), imgUrl, parseFloat(found.alert_threshold || 5));
            barcodeInput.value = '';
            refocusBarcodeInput();
            return;
        }

        // 2. Si non trouvé en local, interroger le serveur via API de recherche
        fetch(`{{ route('caissier.pos.search') }}?barcode=${encodeURIComponent(barcode)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.product) {
                    const p = data.product;
                    const imgUrl = p.image ? `{{ asset('storage') }}/${p.image}` : (p.image_url || null);
                    if (parseFloat(p.stock_quantity) <= 0) {
                        playBeep(false);
                        Toast.fire({
                            icon: 'error',
                            title: `Rupture de stock : ${p.name} est épuisé (Stock: 0) !`
                        });
                    } else {
                        addToCart(p.id, p.name, parseFloat(p.sell_price), parseFloat(p.stock_quantity), imgUrl, parseFloat(p.alert_threshold || 5));
                    }
                } else {
                    playBeep(false);
                    Toast.fire({
                        icon: 'error',
                        title: `Produit inexistant dans notre catalogue (Code: ${barcode})`
                    });
                }
            })
            .catch(err => {
                console.error(err);
                playBeep(false);
                Toast.fire({
                    icon: 'error',
                    title: 'Erreur lors de la recherche du produit.'
                });
            })
            .finally(() => {
                barcodeInput.value = '';
                refocusBarcodeInput();
            });
    }

    // Écoute des événements sur l'input code-barres
    if (barcodeInput) {
        barcodeInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                processBarcode(this.value);
            }
        });
    }

    // Écouteur global pour capture de scan matériel (au cas où le focus s'est déplacé)
    let scanBuffer = '';
    let lastKeyTime = 0;

    window.addEventListener('keydown', function(event) {
        const targetTag = (event.target.tagName || '').toLowerCase();
        const isInput = targetTag === 'input' || targetTag === 'textarea' || targetTag === 'select';
        
        // Si l'utilisateur est déjà dans le champ barcode, laisser le keydown normal agir
        if (event.target === barcodeInput) return;

        // Si l'utilisateur est en train de taper dans un modal ou champ de recherche, ne pas intercepter
        const modal = document.getElementById('payment-modal');
        if (modal && !modal.classList.contains('hidden')) return;
        if (isInput && event.target !== barcodeInput) return;

        const currentTime = Date.now();

        if (event.key === 'Enter') {
            if (scanBuffer.length >= 3) {
                event.preventDefault();
                processBarcode(scanBuffer);
                scanBuffer = '';
            }
        } else if (event.key.length === 1) {
            if (currentTime - lastKeyTime > 150) {
                scanBuffer = '';
            }
            scanBuffer += event.key;
            lastKeyTime = currentTime;
        }
    });

    // Filtre de recherche par nom / référence / code-barres
    function filterProductsByName(term) {
        const clearBtn = document.getElementById('clear-search-btn');
        if (clearBtn) {
            if (term.trim().length > 0) {
                clearBtn.classList.remove('hidden');
            } else {
                clearBtn.classList.add('hidden');
            }
        }

        const cards = document.querySelectorAll('.product-card');
        const q = term.toLowerCase().trim();
        cards.forEach(card => {
            const name = (card.getAttribute('data-name') || '').toLowerCase();
            const barcode = (card.getAttribute('data-barcode') || '').toLowerCase();
            const ref = (card.getAttribute('data-ref') || '').toLowerCase();
            if (!q || name.includes(q) || barcode.includes(q) || ref.includes(q)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    function clearSearch() {
        const input = document.getElementById('search-input');
        if (input) {
            input.value = '';
            filterProductsByName('');
            refocusBarcodeInput();
        }
    }

    // Filtre par catégorie de produit
    function filterCategory(catId) {
        document.querySelectorAll('.category-pill').forEach(btn => {
            btn.classList.remove('bg-[#0056a6]', 'text-white', 'shadow-xs');
            btn.classList.add('bg-slate-100', 'text-slate-700');
        });

        const activeBtn = catId === 'all'
            ? document.querySelector('.category-pill:first-child')
            : document.querySelector(`[data-cat="${catId}"]`);

        if (activeBtn) {
            activeBtn.classList.remove('bg-slate-100', 'text-slate-700');
            activeBtn.classList.add('bg-[#0056a6]', 'text-white', 'shadow-xs');
        }

        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            if (catId === 'all' || card.getAttribute('data-cat') === catId) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });

        refocusBarcodeInput();
    }

    // Modal de paiement
    function openPaymentModal() {
        if (cart.length === 0) {
            Toast.fire({
                icon: 'warning',
                title: 'Le panier est vide !'
            });
            return;
        }
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        document.getElementById('amount-received').value = total;
        document.getElementById('modal-net-total').innerText = total.toLocaleString('fr-FR') + ' FCFA';
        calculateChange();

        document.getElementById('payment-modal').classList.remove('hidden');
        setTimeout(() => {
            document.getElementById('amount-received').select();
        }, 100);
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
        refocusBarcodeInput();
    }

    // ─── GESTION DU MODE DE PAIEMENT ──────────────────────────────────────
    function selectPaymentMode(mode) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const cashCalc = document.getElementById('cash-calc-section');
        const creditInfo = document.getElementById('credit-info-section');
        const amountInput = document.getElementById('amount-received');

        if (mode === 'credit') {
            // Mode crédit : on cache la saisie espèces, on affiche l'info crédit
            cashCalc.classList.add('hidden');
            amountInput.removeAttribute('required');
            creditInfo.classList.remove('hidden');
            document.getElementById('credit-total-display').innerText = total.toLocaleString('fr-FR') + ' FCFA';
        } else {
            // Modes normaux
            cashCalc.classList.remove('hidden');
            amountInput.setAttribute('required', '');
            creditInfo.classList.add('hidden');
            if (mode === 'cash') {
                amountInput.value = total;
            } else {
                // Mobile Money / Carte : montant reçu = total, monnaie = 0
                amountInput.value = total;
            }
            calculateChange();
        }
    }

    function setCashAmount(amt) {
        document.getElementById('amount-received').value = amt;
        calculateChange();
    }

    function calculateChange() {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const received = parseFloat(document.getElementById('amount-received').value) || 0;
        const change = Math.max(0, received - total);
        document.getElementById('change-display').innerText = change.toLocaleString('fr-FR') + ' FCFA';
    }

    // Validation et encaissement de la vente
    function submitSale(event) {
        event.preventDefault();
        const btn = document.getElementById('submit-sale-btn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1"></i> Validation...';

        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const customerId = document.getElementById('customer-select').value;
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

        let amountReceived, creditAmount;
        if (paymentMethod === 'credit') {
            // En mode crédit total : rien n'est encaissé, tout va en dette
            amountReceived = 0;
            creditAmount = total;
        } else {
            amountReceived = parseFloat(document.getElementById('amount-received').value) || 0;
            creditAmount = 0;
        }

        fetch('{{ route('caissier.pos.checkout') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                customer_id: customerId ? customerId : null,
                cart: cart,
                discount: 0,
                payment_method: paymentMethod,
                amount_received: amountReceived,
                credit_amount: creditAmount
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closePaymentModal();
                const printUrl = `{{ url('caissier/pos/receipt') }}/${data.sale_id}`;
                window.open(printUrl, '_blank', 'width=450,height=600');

                const hasLowStock = data.low_stock_alerts && data.low_stock_alerts.length > 0;
                let alertHtml = '<p class="text-xs text-slate-600 mb-3">Le ticket de caisse a été généré avec succès.</p>';

                if (hasLowStock) {
                    alertHtml += `
                        <div class="p-3.5 rounded-2xl bg-amber-50 border border-amber-200 text-left space-y-2 mt-2">
                            <div class="text-xs font-black text-amber-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                                <span>Alerte stock bas après cette vente :</span>
                            </div>
                            <ul class="text-xs text-slate-700 space-y-1.5 pl-1">
                    `;
                    data.low_stock_alerts.forEach(item => {
                        alertHtml += `
                            <li class="font-medium flex items-center justify-between border-b border-amber-100 pb-1">
                                <span class="font-bold text-slate-900 truncate pr-2">• ${item.name}</span>
                                <span class="font-black text-amber-800 bg-amber-100/80 px-2 py-0.5 rounded text-[11px] shrink-0">
                                    ${item.remaining} ${item.unit} restant(s) (seuil : ${item.threshold})
                                </span>
                            </li>
                        `;
                    });
                    alertHtml += `
                            </ul>
                        </div>
                    `;
                }

                Swal.fire({
                    position: 'center',
                    icon: hasLowStock ? 'warning' : 'success',
                    title: hasLowStock ? 'Vente Validée (Alerte Stock)' : 'Vente validée !',
                    html: alertHtml,
                    showCancelButton: true,
                    confirmButtonText: '<i class="fa-solid fa-plus mr-1"></i> Nouvelle vente',
                    cancelButtonText: '<i class="fa-solid fa-motorcycle mr-1"></i> Programmer une livraison',
                    cancelButtonColor: '#0056a6',
                    confirmButtonColor: '#059669',
                    customClass: {
                        popup: 'modern-swal-popup',
                        title: 'modern-swal-title',
                        htmlContainer: 'modern-swal-text',
                        confirmButton: 'modern-swal-confirm',
                        cancelButton: 'modern-swal-cancel',
                        actions: 'modern-swal-actions'
                    }
                }).then((result) => {
                    clearCart();
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = `{{ route('caissier.deliveries.create') }}?sale_id=${data.sale_id}`;
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    position: 'center',
                    icon: 'error',
                    title: 'Erreur',
                    text: data.message || 'Impossible de valider la vente.',
                    confirmButtonText: 'Corriger',
                    customClass: {
                        popup: 'modern-swal-popup',
                        title: 'modern-swal-title',
                        htmlContainer: 'modern-swal-text',
                        confirmButton: 'modern-swal-confirm',
                        actions: 'modern-swal-actions'
                    }
                });
                btn.disabled = false;
                btn.innerText = 'VALIDER & IMPRIMER LE TICKET';
            }
        })
        .catch(err => {
            Swal.fire({
                position: 'center',
                icon: 'error',
                title: 'Erreur technique',
                text: 'Une erreur est survenue lors de la validation.',
                confirmButtonText: 'Fermer',
                customClass: {
                    popup: 'modern-swal-popup',
                    title: 'modern-swal-title',
                    htmlContainer: 'modern-swal-text',
                    confirmButton: 'modern-swal-confirm',
                    actions: 'modern-swal-actions'
                }
            });
            btn.disabled = false;
            btn.innerText = 'VALIDER & IMPRIMER LE TICKET';
        });
    }

    // Afficher/masquer le bouton Crédit selon le client sélectionné
    const customerSelect = document.getElementById('customer-select');

    if (customerSelect) {
        customerSelect.addEventListener('change', function () {
            const creditWrapper = document.getElementById('credit-method-wrapper');
            if (this.value) {
                // Client sélectionné → montrer le bouton Crédit
                creditWrapper.classList.remove('hidden');
            } else {
                // Pas de client → cacher le bouton Crédit et revenir à Espèces
                creditWrapper.classList.add('hidden');
                // Si on était en mode crédit, revenir à espèces
                const currentMode = document.querySelector('input[name="payment_method"]:checked').value;
                if (currentMode === 'credit') {
                    document.querySelector('input[name="payment_method"][value="cash"]').checked = true;
                    selectPaymentMode('cash');
                }
            }
        });
    }

    // Auto-focus au chargement initial
    document.addEventListener('DOMContentLoaded', () => {
        refocusBarcodeInput();
    });
</script>
@endsection

