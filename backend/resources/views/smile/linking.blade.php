@extends('layouts.app')

@section('title', 'Smile Open Banking — Connect Accounts')

@section('header')
<header class="app-header">
    <a href="{{ route('dashboard') }}" class="icon-btn" title="Back to Dashboard">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div style="font-weight: 700; font-size: 1rem;">Smile Open Banking</div>
    <div class="header-actions">
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
    </div>
</header>
@endsection

@section('content')
@php
    $providers = [
        // Commercial Banks
        ['name' => 'BDO Unibank', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#003882', 'sub' => 'Savings & Payroll'],
        ['name' => 'BPI', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#B3141E', 'sub' => 'Express & Payroll'],
        ['name' => 'Metrobank', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#002B7F', 'sub' => 'Commercial Accounts'],
        ['name' => 'Landbank', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#006837', 'sub' => 'Institutional Payroll'],
        ['name' => 'UnionBank', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#FF7A00', 'sub' => 'Freelance Payouts'],
        ['name' => 'RCBC Pulz', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#005BA6', 'sub' => 'Commercial Accounts'],
        ['name' => 'Security Bank', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#00693E', 'sub' => 'BetterBanking'],
        ['name' => 'PNB Digital', 'category' => 'Banks', 'icon' => 'fa-building-columns', 'color' => '#00205B', 'sub' => 'Remittance & Savings'],
        
        // Digital Neobanks
        ['name' => 'Maya Bank', 'category' => 'Digital Banks', 'icon' => 'fa-wallet', 'color' => '#00D18F', 'sub' => 'BSP Licensed Neobank'],
        ['name' => 'GCash Pera Outlet', 'category' => 'Digital Banks', 'icon' => 'fa-mobile-screen-button', 'color' => '#007DFE', 'sub' => 'E-Wallet Transactions'],
        ['name' => 'Tonik Bank', 'category' => 'Digital Banks', 'icon' => 'fa-vault', 'color' => '#802BB1', 'sub' => 'Stash & Deposit Growth'],
        ['name' => 'GoTyme Bank', 'category' => 'Digital Banks', 'icon' => 'fa-money-bill-transfer', 'color' => '#00A86B', 'sub' => 'Robinsons Ecosystem'],

        // Gig Platforms
        ['name' => 'Grab Driver/Food', 'category' => 'Gig Platforms', 'icon' => 'fa-motorcycle', 'color' => '#00B14F', 'sub' => 'Ride & Delivery Earnings'],
        ['name' => 'FoodPanda Rider', 'category' => 'Gig Platforms', 'icon' => 'fa-bowl-food', 'color' => '#D70F64', 'sub' => 'Food Delivery Shifts'],
        ['name' => 'ShopeePay Merchant', 'category' => 'Gig Platforms', 'icon' => 'fa-bag-shopping', 'color' => '#EE4D2D', 'sub' => 'E-Commerce Seller Payouts'],
        ['name' => 'JoyRide / MoveIt', 'category' => 'Gig Platforms', 'icon' => 'fa-helmet-safety', 'color' => '#0052FF', 'sub' => 'Transport Payout History'],
        ['name' => 'Lalamove Driver', 'category' => 'Gig Platforms', 'icon' => 'fa-truck-fast', 'color' => '#F26522', 'sub' => 'Logistics Inflows'],
        ['name' => 'Angkas Rider', 'category' => 'Gig Platforms', 'icon' => 'fa-shield-halved', 'color' => '#00C2FF', 'sub' => 'Two-Wheeled Ride Inflows'],
    ];
@endphp

<main class="app-body">
    <!-- Header Banner -->
    <div class="f-card glow-primary animate-fade-in" style="padding: 18px;">
        <h1 class="title-md" style="font-size: 1.1rem; margin-bottom: 4px;">Link Payout & Banking Accounts</h1>
        <p class="text-subtext" style="font-size: 0.82rem;">
            Connecting your platforms unlocks instant +4 to +8 score points and increases your approved micro-loan ceiling.
        </p>
    </div>

    <!-- Category Filter Pills -->
    <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px;">
        <button type="button" class="tenure-chip selected" onclick="filterCategory('All', this)">All</button>
        <button type="button" class="tenure-chip" onclick="filterCategory('Banks', this)">Banks</button>
        <button type="button" class="tenure-chip" onclick="filterCategory('Digital Banks', this)">Digital Banks</button>
        <button type="button" class="tenure-chip" onclick="filterCategory('Gig Platforms', this)">Gig Platforms</button>
    </div>

    <!-- Search Input -->
    <div class="input-wrapper" style="margin-bottom: 6px;">
        <i class="fa-solid fa-magnifying-glass input-icon"></i>
        <input type="text" id="providerSearch" class="form-control" placeholder="Search Philippine bank or gig app..." oninput="handleSearch(this.value)">
    </div>

    <!-- Provider Grid -->
    <div class="provider-grid" id="providersGrid">
        @foreach($providers as $p)
            @php
                $isLinked = in_array(strtolower(str_replace(' ', '_', $p['name'])) . '_' . $user->id, $linkedAccounts);
            @endphp
            <div class="provider-card {{ $isLinked ? 'connected' : '' }}" 
                 data-category="{{ $p['category'] }}" 
                 data-name="{{ strtolower($p['name']) }}"
                 onclick="connectProvider('{{ $p['name'] }}', '{{ $p['category'] }}')">
                <div class="provider-icon" style="color: {{ $p['color'] }};">
                    <i class="fa-solid {{ $p['icon'] }}"></i>
                </div>
                <div style="font-weight: 700; font-size: 0.82rem; color: var(--text-main); line-height: 1.2;">
                    {{ $p['name'] }}
                </div>
                <div class="text-subtext" style="font-size: 0.68rem;">
                    {{ $p['sub'] }}
                </div>
                @if($isLinked)
                    <span class="badge badge-success" style="font-size: 0.65rem; padding: 2px 6px;">
                        <i class="fa-solid fa-check"></i> Linked
                    </span>
                @else
                    <span style="font-size: 0.7rem; color: var(--primary); font-weight: 600;">
                        + Connect
                    </span>
                @endif
            </div>
        @endforeach
    </div>
</main>

<!-- Connecting Progress Overlay Modal -->
<div id="connectingModal" class="modal-overlay">
    <div class="modal-sheet" style="text-align: center; padding: 32px 20px;">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--card-sub-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 16px auto; border: 1px solid var(--border-color);">
            <i class="fa-solid fa-circle-notch fa-spin"></i>
        </div>
        <h3 id="connectingTitle" class="title-md" style="margin-bottom: 6px;">Connecting Provider...</h3>
        <p class="text-subtext" style="font-size: 0.8rem; max-width: 280px; margin: 0 auto 16px auto;">
            Establishing secure read-only tokenization via Smile Open Banking API & updating XAI credit models...
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let activeCategory = 'All';

    function filterCategory(cat, btn) {
        activeCategory = cat;
        document.querySelectorAll('.tenure-chip').forEach(b => b.classList.remove('selected'));
        btn.classList.add('selected');
        applyFilters();
    }

    function handleSearch(val) {
        applyFilters();
    }

    function applyFilters() {
        const query = document.getElementById('providerSearch').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.provider-card');

        cards.forEach(card => {
            const cat = card.getAttribute('data-category');
            const name = card.getAttribute('data-name');
            const matchCat = (activeCategory === 'All' || cat === activeCategory);
            const matchSearch = (!query || name.includes(query));

            if (matchCat && matchSearch) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    }

    async function connectProvider(providerName, category) {
        openModal('connectingModal');
        document.getElementById('connectingTitle').innerText = `Connecting ${providerName}...`;

        try {
            const res = await fetch("{{ route('smile.connect.submit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ provider: providerName, category: category })
            });

            const data = await res.json();
            if (res.ok) {
                showToast(data.message || `Connected ${providerName}!`, 'success');
                setTimeout(() => {
                    window.location.href = "{{ route('dashboard') }}";
                }, 1000);
            } else {
                closeModal('connectingModal');
                alert(data.message || 'Failed to link provider.');
            }
        } catch (err) {
            closeModal('connectingModal');
            alert('Network error connecting account.');
        }
    }
</script>
@endpush
