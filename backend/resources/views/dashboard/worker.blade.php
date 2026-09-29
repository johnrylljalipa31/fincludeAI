@extends('layouts.app')

@section('title', 'Gig Worker Dashboard — Finclude-AI')

@section('header')

<header class="app-header">

    <div class="brand-title">

        <img
            src="{{ asset('images/finclude_logo.png') }}"
            alt="Finclude-AI Logo"
            class="brand-logo-img"
        >

        <span>Finclude-AI</span>

    </div>

    <div class="header-actions">

        <button
            type="button"
            class="icon-btn"
            onclick="toggleTheme()"
            title="Switch Theme"
            aria-label="Switch Theme"
        >
            <i
                class="fa-solid fa-sun theme-toggle-icon"
                style="color: var(--primary);"
            ></i>
        </button>

        <button
            type="button"
            class="icon-btn"
            onclick="location.reload()"
            title="Refresh & Sync"
            aria-label="Refresh & Sync"
        >
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>

        <a
            href="{{ route('privacy') }}"
            class="icon-btn"
            title="Data Privacy Terms"
            aria-label="Data Privacy Terms"
        >
            <i class="fa-solid fa-shield-halved"></i>
        </a>

        <form
            action="{{ route('logout') }}"
            method="POST"
            style="display: inline;"
        >
            @csrf

            <button
                type="submit"
                class="icon-btn"
                title="Sign Out"
                aria-label="Sign Out"
            >
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>

        </form>

    </div>

</header>

@endsection


@section('content')

@php

    $score = $latestScore
        ? $latestScore->score
        : 82;

    $ceiling = $latestScore
        ? (float) $latestScore->approved_ceiling
        : 25000.00;

    $disposableCash = $latestScore
        ? (float) $latestScore->projected_disposable_cash_flow
        : 18500.00;


    /*
    |--------------------------------------------------------------------------
    | SCORE TIER
    |--------------------------------------------------------------------------
    */

    if ($score >= 80) {

        $scoreColor = '#10B981';
        $scoreGlowClass = 'glow-success';
        $tierName = 'Prime (Low Risk)';

    } elseif ($score >= 60) {

        $scoreColor = '#F59E0B';
        $scoreGlowClass = 'glow-warning';
        $tierName = 'Standard (Moderate Risk)';

    } else {

        $scoreColor = '#EF4444';
        $scoreGlowClass = 'glow-danger';
        $tierName = 'Subprime (High Risk)';

    }


    /*
    |--------------------------------------------------------------------------
    | SHAP FACTORS
    |--------------------------------------------------------------------------
    */

    $factors = [];

    if ($latestScore && !empty($latestScore->shap_factors)) {

        $factors = $latestScore->shap_factors;

        if (is_string($factors)) {
            $factors = json_decode($factors, true) ?? [];
        }

    }


    /*
    |--------------------------------------------------------------------------
    | FALLBACK SHAP FACTORS
    |--------------------------------------------------------------------------
    */

    if (!is_array($factors) || empty($factors)) {

        $factors = [

            [
                'displayText' => 'Strong verified monthly income (₱38,000)',
                'impactPoints' => 13,
                'direction' => 'positive'
            ],

            [
                'displayText' => 'Consistent on-time utility payments',
                'impactPoints' => 10,
                'direction' => 'positive'
            ],

            [
                'displayText' => 'Verified gig platform earnings (Grab/Maya)',
                'impactPoints' => 8,
                'direction' => 'positive'
            ],

            [
                'displayText' => 'Disciplined day-to-day spending trajectory',
                'impactPoints' => 5,
                'direction' => 'positive'
            ],

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE POSITIVE / NEGATIVE POINTS
    |--------------------------------------------------------------------------
    */

    $posPoints = 0;
    $negPoints = 0;

    foreach ($factors as $f) {

        $impact = (float) ($f['impactPoints'] ?? 0);

        if (($f['direction'] ?? 'positive') === 'positive') {

            $posPoints += abs($impact);

        } else {

            $negPoints += abs($impact);

        }

    }

@endphp


<main class="app-body">


    <!-- =========================================================
         HEADER USER PROFILE CARD
         ========================================================= -->

    <div
        class="f-card animate-fade-in"
        style="cursor: pointer;"
        onclick="openModal('workerProfileModal')"
    >

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 12px;
            "
        >

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 14px;
                "
            >

                <div
                    class="profile-pic-wrap"
                    style="
                        width: 50px;
                        height: 50px;
                        border-radius: 50%;
                        overflow: hidden;
                        flex-shrink: 0;
                        background: var(--primary);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.3rem;
                        font-weight: 700;
                        color: #fff;
                        cursor: pointer;
                        position: relative;
                    "
                    title="View Profile"
                >
                    @if($user->profile_picture)
                        <img
                            id="headerProfilePic"
                            src="{{ asset('storage/' . $user->profile_picture) }}"
                            alt="Profile Picture"
                            style="width:100%;height:100%;object-fit:cover;display:block;"
                        >
                    @else
                        <span id="headerProfileInitials">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <img id="headerProfilePic" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
                    @endif
                </div>


                <div>

                    <div
                        style="
                            display: flex;
                            align-items: center;
                            gap: 6px;
                        "
                    >

                        <span
                            id="userNameDisplay"
                            class="title-md"
                            style="font-size: 1.05rem;"
                        >
                            {{ $user->name }}
                        </span>


                        <button
                            type="button"
                            class="icon-btn"
                            style="
                                width: 24px;
                                height: 24px;
                                font-size: 0.85rem;
                            "
                            onclick="event.stopPropagation(); toggleUserInfoMask();"
                            title="Show/Hide Personal Information"
                            aria-label="Show/Hide Personal Information"
                        >

                            <i
                                id="userMaskIcon"
                                class="fa-regular fa-eye"
                            ></i>

                        </button>

                    </div>


                    <div
                        id="userEmailDisplay"
                        class="text-subtext"
                        style="font-size: 0.8rem;"
                    >
                        {{ $user->email }}
                    </div>

                </div>

            </div>


            <div
                style="
                    display: flex;
                    flex-direction: column;
                    align-items: flex-end;
                    gap: 4px;
                "
            >

                <span
                    class="badge badge-primary"
                    style="font-size: 0.72rem;"
                >
                    RA 10173 Verified
                </span>

                <span
                    style="
                        font-size: 0.75rem;
                        color: var(--primary);
                        font-weight: 600;
                        display: flex;
                        align-items: center;
                        gap: 2px;
                    "
                >
                    View Profile

                    <i
                        class="fa-solid fa-chevron-right"
                        style="font-size: 0.65rem;"
                    ></i>

                </span>

            </div>

        </div>

    </div>


    <!-- =========================================================
         CURRENT AVAILABLE FUNDS
         ========================================================= -->

    <div class="f-card glow-primary animate-fade-in">

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 8px;
            "
        >

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 10px;
                "
            >

                <div
                    style="
                        background: rgba(0, 229, 255, 0.15);
                        color: var(--primary);
                        padding: 8px;
                        border-radius: 10px;
                        font-size: 1rem;
                    "
                >
                    <i class="fa-solid fa-wallet"></i>
                </div>

                <span
                    class="text-caption"
                    style="color: var(--primary);"
                >
                    Current Available Funds
                </span>

            </div>


            <button
                type="button"
                class="icon-btn"
                onclick="toggleWalletBalance()"
                title="Show/Hide Available Funds"
                aria-label="Show/Hide Available Funds"
            >

                <i
                    id="walletEyeIcon"
                    class="fa-regular fa-eye"
                ></i>

            </button>

        </div>


        <div
            id="walletBalanceDisplay"
            class="title-xl"
            style="
                font-size: 2.2rem;
                margin: 8px 0;
            "
        >
            ₱{{ number_format($walletBalance, 2) }}
        </div>


        <div
            style="
                display: flex;
                align-items: center;
                gap: 6px;
                font-size: 0.8rem;
                color: var(--text-muted);
                margin-bottom: 16px;
            "
        >

            <span
                style="
                    width: 8px;
                    height: 8px;
                    border-radius: 50%;
                    background: var(--success);
                    display: inline-block;
                "
            ></span>

            <span>
                Current available funds from recorded cash-flow activity
            </span>

        </div>


        <div
            style="
                font-size: 0.72rem;
                color: var(--text-muted);
                margin-left: 14px;
                margin-bottom: 16px;
            "
        >
            This is your current available cash based on recorded
            cash-flow activity. It is separate from your approved loan amount.
        </div>


        <div
            style="
                border-top: 1px solid var(--border-color);
                padding-top: 14px;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            "
        >

            <!-- Total Inflows -->

            <div
                class="f-card-sub"
                style="padding: 10px 12px;"
            >

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.76rem;
                        color: var(--text-muted);
                    "
                >

                    <i
                        class="fa-solid fa-arrow-down"
                        style="color: var(--success);"
                    ></i>

                    <span>Total Inflows</span>

                </div>


                <div
                    id="inflowsDisplay"
                    style="
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--success);
                        margin-top: 2px;
                    "
                >
                    +₱{{ number_format($totalCredits, 2) }}
                </div>

            </div>


            <!-- Total Outflows -->

            <div
                class="f-card-sub"
                style="padding: 10px 12px;"
            >

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.76rem;
                        color: var(--text-muted);
                    "
                >

                    <i
                        class="fa-solid fa-arrow-up"
                        style="color: var(--danger);"
                    ></i>

                    <span>Total Outflows</span>

                </div>


                <div
                    id="outflowsDisplay"
                    style="
                        font-weight: 700;
                        font-size: 0.95rem;
                        color: var(--danger);
                        margin-top: 2px;
                    "
                >
                    -₱{{ number_format($totalDebits, 2) }}
                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         RADIAL CREDIT SCORE
         ========================================================= -->

    <div
        class="f-card {{ $scoreGlowClass }} animate-fade-in"
        style="text-align: center;"
    >

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                margin-bottom: 12px;
            "
        >

            <i
                class="fa-solid fa-wand-magic-sparkles"
                style="color: {{ $scoreColor }};"
            ></i>

            <span
                class="text-caption"
                style="color: {{ $scoreColor }};"
            >
                XAI Alternative Credit Score
            </span>

        </div>


        <!-- Animated SVG Gauge -->

        <div class="gauge-container">

            <div class="gauge-svg-wrap">

                <svg
                    class="gauge-svg"
                    viewBox="0 0 140 140"
                >

                    <circle
                        class="gauge-bg-circle"
                        cx="70"
                        cy="70"
                        r="56"
                    ></circle>


                    <circle
                        class="gauge-fill-circle"
                        cx="70"
                        cy="70"
                        r="56"
                        stroke="{{ $scoreColor }}"
                        stroke-dasharray="351.8"
                        stroke-dashoffset="{{ 351.8 - (351.8 * ($score / 100)) }}"
                    ></circle>

                </svg>


                <div class="gauge-center-text">

                    <span
                        class="gauge-score-num"
                        style="color: {{ $scoreColor }};"
                    >
                        {{ $score }}
                    </span>

                    <span class="gauge-score-denom">
                        /100
                    </span>

                </div>

            </div>

        </div>


        <div style="margin: 12px 0;">

            <span
                class="badge"
                style="
                    background: {{ $scoreColor }};
                    color: #0B1325;
                    font-size: 0.85rem;
                    padding: 6px 14px;
                "
            >
                Risk Tier: {{ $tierName }}
            </span>

        </div>


        <p
            class="text-subtext"
            style="
                font-size: 0.85rem;
                margin-bottom: 16px;
            "
        >
            Projected Net Disposable Cash:

            <strong>
                ₱{{ number_format($disposableCash, 2) }} / mo
            </strong>
        </p>


        <!-- XAI Score Attribution Breakdown -->

        <div
            class="f-card-sub"
            style="
                display: flex;
                justify-content: space-around;
                align-items: center;
                padding: 12px;
            "
        >

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                "
            >

                <i
                    class="fa-solid fa-arrow-trend-up"
                    style="
                        color: var(--success);
                        font-size: 1.1rem;
                    "
                ></i>

                <div style="text-align: left;">

                    <div
                        style="
                            font-weight: 800;
                            font-size: 0.95rem;
                            color: var(--success);
                        "
                    >
                        +{{ $posPoints }} pts
                    </div>

                    <div
                        class="text-subtext"
                        style="font-size: 0.72rem;"
                    >
                        Positive Signals
                    </div>

                </div>

            </div>


            <div
                style="
                    width: 1px;
                    height: 26px;
                    background: var(--border-color);
                "
            ></div>


            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 8px;
                "
            >

                <i
                    class="fa-solid fa-arrow-trend-down"
                    style="
                        color: var(--danger);
                        font-size: 1.1rem;
                    "
                ></i>

                <div style="text-align: left;">

                    <div
                        style="
                            font-weight: 800;
                            font-size: 0.95rem;
                            color: var(--danger);
                        "
                    >
                        -{{ $negPoints }} pts
                    </div>

                    <div
                        class="text-subtext"
                        style="font-size: 0.72rem;"
                    >
                        Risk Deductions
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         FINANCIAL STABILITY & TRENDS
         ========================================================= -->

    <div class="f-card animate-fade-in">

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 14px;
            "
        >

            <h2 class="title-md">
                Financial Stability & Trends
            </h2>

            <span class="badge badge-success">
                High Reliability
            </span>

        </div>


        <div
            style="
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-bottom: 16px;
            "
        >

            <div
                class="f-card-sub"
                style="padding: 12px;"
            >

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.76rem;
                        color: var(--text-muted);
                        margin-bottom: 4px;
                    "
                >

                    <i
                        class="fa-solid fa-gauge-high"
                        style="color: var(--success);"
                    ></i>

                    <span>Burn Rate</span>

                </div>


                <div
                    style="
                        font-size: 1.25rem;
                        font-weight: 800;
                        color: var(--text-main);
                    "
                >
                    0.85x
                </div>


                <div
                    style="
                        font-size: 0.72rem;
                        color: var(--success);
                        font-weight: 600;
                    "
                >
                    Controlled spending
                </div>

            </div>


            <div
                class="f-card-sub"
                style="padding: 12px;"
            >

                <div
                    style="
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        font-size: 0.76rem;
                        color: var(--text-muted);
                        margin-bottom: 4px;
                    "
                >

                    <i
                        class="fa-solid fa-chart-line"
                        style="color: var(--primary);"
                    ></i>

                    <span>Consistency</span>

                </div>


                <div
                    style="
                        font-size: 1.25rem;
                        font-weight: 800;
                        color: var(--text-main);
                    "
                >
                    92%
                </div>


                <div
                    style="
                        font-size: 0.72rem;
                        color: var(--primary);
                        font-weight: 600;
                    "
                >
                    Predictable payouts
                </div>

            </div>

        </div>


        <div
            class="text-caption"
            style="margin-bottom: 8px;"
        >
            Cash-Flow Trajectory (Past 4 Weeks)
        </div>


        <div class="cashflow-chart">

            <div class="cashflow-col">

                <div
                    class="cashflow-amount"
                    style="color: var(--primary);"
                >
                    ₱28k
                </div>

                <div
                    class="cashflow-bar"
                    style="height: 48px;"
                ></div>

                <div class="cashflow-label">
                    W1
                </div>

            </div>


            <div class="cashflow-col">

                <div
                    class="cashflow-amount"
                    style="color: var(--primary);"
                >
                    ₱34k
                </div>

                <div
                    class="cashflow-bar"
                    style="height: 60px;"
                ></div>

                <div class="cashflow-label">
                    W2
                </div>

            </div>


            <div class="cashflow-col">

                <div
                    class="cashflow-amount"
                    style="color: var(--primary);"
                >
                    ₱31k
                </div>

                <div
                    class="cashflow-bar"
                    style="height: 54px;"
                ></div>

                <div class="cashflow-label">
                    W3
                </div>

            </div>


            <div class="cashflow-col">

                <div
                    class="cashflow-amount"
                    style="color: var(--success);"
                >
                    ₱38k
                </div>

                <div
                    class="cashflow-bar current"
                    style="height: 72px;"
                ></div>

                <div class="cashflow-label">
                    W4 (Now)
                </div>

            </div>

        </div>

    </div>


    <!-- =========================================================
         CURRENT LOAN OFFER CEILING
         ========================================================= -->

    <div
        class="f-card {{ $scoreGlowClass }} animate-fade-in"
        style="
            background:
                linear-gradient(
                    135deg,
                    var(--card-bg) 0%,
                    var(--card-sub-bg) 100%
                );
        "
    >

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            "
        >

            <div>

                <span
                    class="text-caption"
                    style="color: {{ $scoreColor }};"
                >
                    Current Loan Offer Ceiling
                </span>


                <div
                    class="title-xl"
                    style="
                        font-size: 1.85rem;
                        color: {{ $scoreColor }};
                        margin: 4px 0;
                    "
                >
                    ₱{{ number_format($ceiling, 2) }}
                </div>


                <div
                    class="text-subtext"
                    style="font-size: 0.78rem;"
                >
                    Maximum amount available to request
                    • 24% APR • 61–180 Days
                </div>


                <div
                    style="
                        font-size: 0.72rem;
                        color: var(--text-muted);
                        margin-top: 5px;
                    "
                >
                    This is a loan offer limit, separate from your current available funds.
                </div>

            </div>


            <button
                type="button"
                class="btn-primary"
                style="
                    width: auto;
                    padding: 12px 18px;
                    background: {{ $scoreColor }};
                    color: #0B1325;
                "
                onclick="openModal('loanRequestModal')"
            >

                <i class="fa-solid fa-hand-holding-dollar"></i>

                <span>
                    Request Loan
                </span>

            </button>

        </div>

    </div>


    <!-- =========================================================
         EXPLAINABLE AI SHAP SCORE FACTORS
         ========================================================= -->

    <div>

        <div
            style="
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 12px;
            "
        >

            <h2 class="title-md">
                Explainable AI (SHAP) Score Factors
            </h2>

            <span
                style="
                    font-size: 0.75rem;
                    color: var(--text-muted);
                "
            >
                {{ count($factors) }} active factors
            </span>

        </div>


        @foreach($factors as $factor)

            @php

                $isPos =
                    ($factor['direction'] ?? 'positive') === 'positive';

                $pts =
                    abs((float) ($factor['impactPoints'] ?? 0));

                $percent =
                    min(100, ($pts / 15) * 100);

            @endphp


            <div
                class="shap-factor-row {{ $isPos ? 'positive' : 'negative' }} animate-fade-in"
            >

                <div class="shap-header">

                    <div class="shap-title">

                        <i
                            class="fa-solid {{ $isPos ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"
                            style="
                                color:
                                {{ $isPos
                                    ? 'var(--success)'
                                    : 'var(--danger)' }};
                            "
                        ></i>

                        <span>
                            {{ $factor['displayText'] ?? 'Factor attribution' }}
                        </span>

                    </div>


                    <span
                        class="shap-badge {{ $isPos ? 'positive' : 'negative' }}"
                    >
                        {{ $isPos ? '+' : '-' }}{{ $pts }} pts
                    </span>

                </div>


                <div class="shap-bar-bg">

                    <div
                        class="shap-bar-fill {{ $isPos ? 'positive' : 'negative' }}"
                        style="width: {{ $percent }}%;"
                    ></div>

                </div>

            </div>

        @endforeach

    </div>


    <!-- =========================================================
         QUICK ACTION BUTTONS
         ========================================================= -->

    <div
        style="
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 8px;
        "
    >

        <a
            href="{{ route('loan.lifecycle') }}"
            class="btn-outline-primary"
            style="padding: 14px;"
        >

            <i class="fa-solid fa-clock-rotate-left"></i>

            <span>
                View Active Loan & Repayment Lifecycle
            </span>

        </a>


        <a
            href="{{ route('smile.link') }}"
            class="btn-secondary"
            style="padding: 14px;"
        >

            <i class="fa-solid fa-link"></i>

            <span>
                Connect Accounts (Smile Open Banking)
            </span>

        </a>

    </div>

</main>


<!-- =============================================================
     INTERACTIVE MICRO-LOAN REQUEST MODAL
     ============================================================= -->

<div
    id="loanRequestModal"
    class="modal-overlay"
>

    <div class="modal-sheet">

        <div class="modal-header">

            <div>

                <h3 class="title-md">
                    Request Micro-Loan
                </h3>

                <div
                    style="
                        font-size: 0.8rem;
                        color: var(--primary);
                        font-weight: 700;
                    "
                >
                    Maximum Loan Offer:
                    ₱{{ number_format($ceiling, 2) }}
                </div>

            </div>


            <button
                type="button"
                class="modal-close-btn"
                onclick="closeModal('loanRequestModal')"
                aria-label="Close Loan Request"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <form
            action="{{ route('loan.apply.submit') }}"
            method="POST"
            id="loanApplyForm"
            onsubmit="handleLoanSubmit(event)"
        >

            @csrf


            <!-- Loan Amount Slider -->

            <div style="margin-bottom: 16px;">

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 6px;
                    "
                >

                    <span
                        class="form-label"
                        style="margin: 0;"
                    >
                        Requested Loan Amount
                    </span>


                    <span
                        id="sliderValueText"
                        style="
                            font-size: 1.25rem;
                            font-weight: 800;
                            color: var(--primary);
                        "
                    >
                        ₱{{ number_format(min(5000, $ceiling), 2) }}
                    </span>

                </div>


                <input
                    type="range"
                    id="loanAmountSlider"
                    name="amount"
                    class="f-slider"
                    min="1000"
                    max="{{ $ceiling }}"
                    step="500"
                    value="{{ min(5000, $ceiling) }}"
                    oninput="updateLoanCalculations()"
                >


                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.75rem;
                        color: var(--text-muted);
                        margin-top: 4px;
                    "
                >

                    <span>₱1,000.00</span>

                    <span>
                        Up to ₱{{ number_format($ceiling, 2) }}
                    </span>

                </div>

            </div>


            <!-- Tenure Selection Chips -->

            <div style="margin-bottom: 16px;">

                <div
                    class="form-label"
                    style="margin-bottom: 6px;"
                >
                    Repayment Tenure
                </div>


                <div class="tenure-chips-row">

                    <div
                        class="tenure-chip"
                        data-days="61"
                        onclick="selectTenure(61)"
                    >
                        61 d
                    </div>


                    <div
                        class="tenure-chip selected"
                        data-days="90"
                        onclick="selectTenure(90)"
                    >
                        90 d
                    </div>


                    <div
                        class="tenure-chip"
                        data-days="120"
                        onclick="selectTenure(120)"
                    >
                        120 d
                    </div>


                    <div
                        class="tenure-chip"
                        data-days="180"
                        onclick="selectTenure(180)"
                    >
                        180 d
                    </div>

                </div>


                <input
                    type="hidden"
                    name="tenure_days"
                    id="selectedTenureInput"
                    value="90"
                >

            </div>


            <!-- Calculation Summary Box -->

            <div
                class="f-card-sub"
                style="
                    margin-bottom: 16px;
                    padding: 14px;
                "
            >

                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.82rem;
                        margin-bottom: 6px;
                    "
                >

                    <span style="color: var(--text-muted);">
                        Interest Rate (Fixed APR)
                    </span>

                    <span style="font-weight: 600;">
                        24.0% per annum
                    </span>

                </div>


                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.82rem;
                        margin-bottom: 6px;
                    "
                >

                    <span style="color: var(--text-muted);">
                        Estimated Total Interest
                    </span>

                    <span
                        id="calcInterestText"
                        style="
                            font-weight: 700;
                            color: var(--warning);
                        "
                    >
                        ₱295.89
                    </span>

                </div>


                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.82rem;
                        margin-bottom: 6px;
                    "
                >

                    <span style="color: var(--text-muted);">
                        Total Repayment
                    </span>

                    <span
                        id="calcTotalText"
                        style="
                            font-weight: 800;
                            color: var(--text-main);
                        "
                    >
                        ₱5,295.89
                    </span>

                </div>


                <div
                    style="
                        border-top: 1px dashed var(--border-color);
                        margin-top: 6px;
                        padding-top: 6px;
                        display: flex;
                        justify-content: space-between;
                        font-size: 0.88rem;
                    "
                >

                    <span
                        style="
                            font-weight: 600;
                            color: var(--primary);
                        "
                    >
                        Estimated Monthly Amortization
                    </span>

                    <span
                        id="calcMonthlyText"
                        style="
                            font-weight: 800;
                            color: var(--primary);
                        "
                    >
                        ₱1,765.30 / mo
                    </span>

                </div>

            </div>


            <!-- Purpose Input -->

            <div
                class="form-group"
                style="margin-bottom: 20px;"
            >

                <label
                    class="form-label"
                    for="purpose"
                >
                    Loan Purpose
                </label>


                <div class="input-wrapper">

                    <i class="fa-solid fa-pen-to-square input-icon"></i>

                    <input
                        type="text"
                        id="purpose"
                        name="purpose"
                        class="form-control"
                        value="Working capital & utility cash cushion"
                        placeholder="e.g. Motorcycle maintenance / Store inventory"
                        required
                    >

                </div>

            </div>


            <button
                type="submit"
                id="loanSubmitBtn"
                class="btn-primary"
                style="padding: 15px;"
            >

                <span>
                    Submit Loan Application
                </span>

                <i class="fa-solid fa-paper-plane"></i>

            </button>

        </form>

    </div>

</div>


<!-- =============================================================
     WORKER PROFILE BOTTOM SHEET MODAL
     ============================================================= -->

<div
    id="workerProfileModal"
    class="modal-overlay"
>

    <div class="modal-sheet">

        <div class="modal-header">

            <div
                style="
                    display: flex;
                    align-items: center;
                    gap: 10px;
                "
            >

                <div
                    style="
                        width: 42px;
                        height: 42px;
                        border-radius: 50%;
                        overflow: hidden;
                        flex-shrink: 0;
                        background: var(--primary);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        font-size: 1.1rem;
                        font-weight: 700;
                        color: #fff;
                    "
                >
                    @if($user->profile_picture)
                        <img
                            id="modalProfilePic"
                            src="{{ asset('storage/' . $user->profile_picture) }}"
                            alt="Profile Picture"
                            style="width:100%;height:100%;object-fit:cover;display:block;"
                        >
                    @else
                        <span id="modalProfileInitials">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        <img id="modalProfilePic" src="" alt="" style="display:none;width:100%;height:100%;object-fit:cover;">
                    @endif
                </div>


                <div>

                    <h3 class="title-md">
                        {{ $user->name }}
                    </h3>

                    <p
                        class="text-subtext"
                        style="font-size: 0.78rem;"
                    >
                        Gig Worker Profile & Credentials
                    </p>

                </div>

            </div>


            <button
                type="button"
                class="modal-close-btn"
                onclick="closeModal('workerProfileModal')"
                aria-label="Close Worker Profile"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        {{-- Profile Picture Upload --}}
        <div
            class="f-card-sub"
            style="
                margin-bottom: 18px;
                padding: 14px;
                display: flex;
                align-items: center;
                gap: 14px;
            "
        >

            {{-- Large Avatar Preview --}}
            <div
                id="avatarPreviewWrap"
                style="
                    width: 72px;
                    height: 72px;
                    border-radius: 50%;
                    overflow: hidden;
                    flex-shrink: 0;
                    background: var(--primary);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 1.8rem;
                    font-weight: 700;
                    color: #fff;
                    border: 3px solid var(--primary);
                    cursor: pointer;
                "
                onclick="document.getElementById('picFileInput').click()"
                title="Click to change profile picture"
            >
                @if($user->profile_picture)
                    <img
                        id="avatarPreviewImg"
                        src="{{ asset('storage/' . $user->profile_picture) }}"
                        alt="Profile Picture"
                        style="width:100%;height:100%;object-fit:cover;display:block;"
                    >
                @else
                    <img
                        id="avatarPreviewImg"
                        src=""
                        alt=""
                        style="display:none;width:100%;height:100%;object-fit:cover;"
                    >
                @endif
                <span
                    id="avatarPreviewInitials"
                    style="{{ $user->profile_picture ? 'display:none;' : '' }}"
                >
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
            </div>


            {{-- Upload Controls --}}
            <div style="flex: 1; min-width: 0;">

                <div
                    style="
                        font-size: 0.78rem;
                        font-weight: 600;
                        color: var(--text-main);
                        margin-bottom: 6px;
                    "
                >
                    Profile Picture
                </div>

                <input
                    type="file"
                    id="picFileInput"
                    accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                    style="display: none;"
                    onchange="handlePicSelect(event)"
                >

                <button
                    type="button"
                    id="uploadPicBtn"
                    class="btn-primary"
                    style="
                        font-size: 0.75rem;
                        padding: 6px 14px;
                        margin-right: 6px;
                    "
                    onclick="document.getElementById('picFileInput').click()"
                >
                    <i class="fa-solid fa-camera" style="margin-right: 6px;"></i>Change Photo
                </button>

                @if($user->profile_picture)
                <button
                    type="button"
                    id="removePicBtn"
                    class="btn-secondary"
                    style="
                        font-size: 0.75rem;
                        padding: 6px 14px;
                    "
                    onclick="removeProfilePicture()"
                >
                    <i class="fa-solid fa-trash" style="margin-right: 5px;"></i>Remove
                </button>
                @endif

                <div
                    id="picUploadStatus"
                    style="
                        font-size: 0.72rem;
                        margin-top: 6px;
                        color: var(--text-muted);
                    "
                >
                    JPG, PNG, GIF, WebP — max 5 MB
                </div>

            </div>

        </div>


        <form
            action="{{ route('profile.update') }}"
            method="POST"
            id="profileUpdateForm"
            onsubmit="handleProfileUpdate(event)"
        >

            @csrf


            <div class="form-group">

                <label
                    class="form-label"
                    for="profile_name"
                >
                    Full Legal Name
                </label>

                <input
                    type="text"
                    id="profile_name"
                    name="name"
                    class="form-control"
                    style="padding-left: 14px;"
                    value="{{ $user->name }}"
                    required
                >

            </div>


            <div class="form-group">

                <label
                    class="form-label"
                    for="profile_email"
                >
                    Registered Email
                </label>

                <input
                    type="email"
                    id="profile_email"
                    class="form-control"
                    style="
                        padding-left: 14px;
                        opacity: 0.7;
                    "
                    value="{{ $user->email }}"
                    disabled
                >

            </div>


            <div class="form-group">

                <label
                    class="form-label"
                    for="profile_phone"
                >
                    Mobile Contact
                </label>

                <input
                    type="text"
                    id="profile_phone"
                    name="phone_number"
                    class="form-control"
                    style="padding-left: 14px;"
                    value="{{ $user->phone_number }}"
                    placeholder="e.g. +63 917 123 4567"
                >

            </div>


            <div class="form-group">

                <label
                    class="form-label"
                    for="profile_id_type"
                >
                    Government ID Type
                </label>

                <input
                    type="text"
                    id="profile_id_type"
                    name="id_type"
                    class="form-control"
                    style="padding-left: 14px;"
                    value="{{ $user->id_type ?? 'Philippine National ID (PhilSys)' }}"
                >

            </div>


            <div
                class="form-group"
                style="margin-bottom: 20px;"
            >

                <label
                    class="form-label"
                    for="profile_id_number"
                >
                    ID Number / Serial
                </label>

                <input
                    type="text"
                    id="profile_id_number"
                    name="id_number"
                    class="form-control"
                    style="padding-left: 14px;"
                    value="{{ $user->id_number }}"
                    placeholder="e.g. 1234-5678-9012-3456"
                >

            </div>


            <button
                type="submit"
                id="saveProfileBtn"
                class="btn-primary"
                style="margin-bottom: 12px;"
            >
                Save Profile Changes
            </button>

        </form>


        <div
            class="f-card-sub"
            style="
                margin-top: 10px;
                font-size: 0.78rem;
                color: var(--text-muted);
            "
        >

            <div
                style="
                    font-weight: 700;
                    color: var(--text-main);
                    margin-bottom: 4px;
                "
            >
                Data Protection & Exercise of Rights
            </div>

            Under RA 10173, your data is processed exclusively for credit evaluation.
            You may revoke consent or request record erasure after loan settlement.

        </div>

    </div>

</div>

@endsection


@push('scripts')

<script>

    let isMasked = false;
    let isWalletHidden = false;


    /*
    |--------------------------------------------------------------------------
    | RAW USER DATA
    |--------------------------------------------------------------------------
    */

    const rawName = @json($user->name);

    const rawEmail = @json($user->email);

    const rawWallet = @json(
        number_format($walletBalance, 2)
    );

    const rawInflows = @json(
        '+₱' . number_format($totalCredits, 2)
    );

    const rawOutflows = @json(
        '-₱' . number_format($totalDebits, 2)
    );


    /*
    |--------------------------------------------------------------------------
    | USER INFORMATION MASK
    |--------------------------------------------------------------------------
    */

    function toggleUserInfoMask() {

        isMasked = !isMasked;

        const nameEl =
            document.getElementById('userNameDisplay');

        const emailEl =
            document.getElementById('userEmailDisplay');

        const icon =
            document.getElementById('userMaskIcon');


        if (!nameEl || !emailEl || !icon) {
            return;
        }


        if (isMasked) {

            nameEl.innerText =
                '• • • • • • • •';

            emailEl.innerText =
                '••••••••••••@••••.com';

            icon.classList.remove('fa-eye');

            icon.classList.add('fa-eye-slash');

        } else {

            nameEl.innerText =
                rawName;

            emailEl.innerText =
                rawEmail;

            icon.classList.remove('fa-eye-slash');

            icon.classList.add('fa-eye');

        }

    }


    /*
    |--------------------------------------------------------------------------
    | WALLET BALANCE MASK
    |--------------------------------------------------------------------------
    */

    function toggleWalletBalance() {

        isWalletHidden = !isWalletHidden;

        const balanceEl =
            document.getElementById('walletBalanceDisplay');

        const inEl =
            document.getElementById('inflowsDisplay');

        const outEl =
            document.getElementById('outflowsDisplay');

        const icon =
            document.getElementById('walletEyeIcon');


        if (
            !balanceEl ||
            !inEl ||
            !outEl ||
            !icon
        ) {
            return;
        }


        if (isWalletHidden) {

            balanceEl.innerText =
                '₱ • • • • • •';

            inEl.innerText =
                '+₱ • • • •';

            outEl.innerText =
                '-₱ • • • •';

            icon.classList.remove('fa-eye');

            icon.classList.add('fa-eye-slash');

        } else {

            balanceEl.innerText =
                '₱' + rawWallet;

            inEl.innerText =
                rawInflows;

            outEl.innerText =
                rawOutflows;

            icon.classList.remove('fa-eye-slash');

            icon.classList.add('fa-eye');

        }

    }


    /*
    |--------------------------------------------------------------------------
    | LOAN TENURE
    |--------------------------------------------------------------------------
    */

    let selectedTenure = 90;


    function selectTenure(days) {

        selectedTenure = parseInt(days, 10);

        const tenureInput =
            document.getElementById(
                'selectedTenureInput'
            );


        if (tenureInput) {

            tenureInput.value =
                selectedTenure;

        }


        document
            .querySelectorAll('.tenure-chip')
            .forEach(chip => {

                const chipDays =
                    parseInt(
                        chip.getAttribute('data-days'),
                        10
                    );


                if (chipDays === selectedTenure) {

                    chip.classList.add('selected');

                } else {

                    chip.classList.remove('selected');

                }

            });


        updateLoanCalculations();

    }


    /*
    |--------------------------------------------------------------------------
    | LOAN CALCULATIONS
    |--------------------------------------------------------------------------
    */

    function updateLoanCalculations() {

        const slider =
            document.getElementById(
                'loanAmountSlider'
            );


        if (!slider) {
            return;
        }


        const amount =
            parseFloat(slider.value) || 0;


        const sliderValueText =
            document.getElementById(
                'sliderValueText'
            );

        const calcInterestText =
            document.getElementById(
                'calcInterestText'
            );

        const calcTotalText =
            document.getElementById(
                'calcTotalText'
            );

        const calcMonthlyText =
            document.getElementById(
                'calcMonthlyText'
            );


        if (
            !sliderValueText ||
            !calcInterestText ||
            !calcTotalText ||
            !calcMonthlyText
        ) {
            return;
        }


        sliderValueText.innerText =
            '₱' +
            amount.toLocaleString(
                'en-PH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        const apr = 0.24;


        const interest =
            amount *
            apr *
            (selectedTenure / 365.0);


        const total =
            amount + interest;


        const months =
            selectedTenure / 30.0;


        const monthly =
            months > 0
                ? total / months
                : total;


        calcInterestText.innerText =
            '₱' +
            interest.toLocaleString(
                'en-PH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        calcTotalText.innerText =
            '₱' +
            total.toLocaleString(
                'en-PH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            );


        calcMonthlyText.innerText =
            '₱' +
            monthly.toLocaleString(
                'en-PH',
                {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }
            ) +
            ' / mo';

    }


    /*
    |--------------------------------------------------------------------------
    | AJAX LOAN APPLICATION
    |--------------------------------------------------------------------------
    */

    async function handleLoanSubmit(e) {

        e.preventDefault();


        const form =
            e.target;


        const submitBtn =
            document.getElementById(
                'loanSubmitBtn'
            );


        if (!submitBtn || !form) {
            return;
        }


        submitBtn.disabled = true;

        submitBtn.innerHTML =
            '<i class="fa-solid fa-circle-notch fa-spin"></i> Submitting...';


        const amount =
            document.getElementById(
                'loanAmountSlider'
            ).value;


        const tenure_days =
            document.getElementById(
                'selectedTenureInput'
            ).value;


        const purpose =
            document.getElementById(
                'purpose'
            ).value;


        try {

            const csrfTokenElement =
                document.querySelector(
                    'meta[name="csrf-token"]'
                );


            const csrfToken =
                csrfTokenElement
                    ? csrfTokenElement.content
                    : form.querySelector(
                        'input[name="_token"]'
                    )?.value;


            if (!csrfToken) {

                throw new Error(
                    'CSRF token not found.'
                );

            }


            const res =
                await fetch(
                    "{{ route('loan.apply.submit') }}",
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify({
                                amount,
                                tenure_days,
                                purpose
                            })
                    }
                );


            const data =
                await res
                    .json()
                    .catch(() => ({}));


            if (res.ok) {

                closeModal(
                    'loanRequestModal'
                );


                showToast(
                    data.message ||
                    'Loan request submitted successfully!',
                    'success'
                );


                setTimeout(() => {

                    window.location.href =
                        "{{ route('loan.lifecycle') }}";

                }, 1200);


            } else {

                alert(
                    data.message ||
                    'Failed to submit loan request.'
                );


                submitBtn.disabled = false;

                submitBtn.innerHTML =
                    '<span>Submit Loan Application</span>' +
                    ' <i class="fa-solid fa-paper-plane"></i>';

            }


        } catch (err) {

            console.error(
                'Loan submission error:',
                err
            );


            alert(
                'Network connection error while submitting loan application.'
            );


            submitBtn.disabled = false;

            submitBtn.innerHTML =
                '<span>Submit Loan Application</span>' +
                ' <i class="fa-solid fa-paper-plane"></i>';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | AJAX PROFILE UPDATE
    |--------------------------------------------------------------------------
    */

    /* ─── Profile Picture helpers ──────────────────────────── */

    function handlePicSelect(event) {
        const file = event.target.files[0];
        if (!file) return;

        const MAX = 5 * 1024 * 1024;
        if (file.size > MAX) {
            document.getElementById('picUploadStatus').textContent = 'File too large (max 5 MB).';
            document.getElementById('picUploadStatus').style.color = 'var(--danger)';
            return;
        }

        // Live preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const src = e.target.result;
            ['avatarPreviewImg', 'headerProfilePic', 'modalProfilePic'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.src = src; el.style.display = 'block'; }
            });
            ['avatarPreviewInitials', 'headerProfileInitials', 'modalProfileInitials'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.style.display = 'none';
            });
        };
        reader.readAsDataURL(file);

        // Upload immediately
        uploadProfilePicture(file);
    }

    async function uploadProfilePicture(file) {
        const statusEl = document.getElementById('picUploadStatus');
        const btn = document.getElementById('uploadPicBtn');
        if (btn) btn.disabled = true;
        if (statusEl) { statusEl.textContent = 'Uploading...'; statusEl.style.color = 'var(--primary)'; }

        const formData = new FormData();
        formData.append('profile_picture', file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value || '');

        try {
            const res = await fetch("{{ route('profile.picture') }}", {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': formData.get('_token') },
                body: formData,
            });

            const data = await res.json().catch(() => ({}));

            if (res.ok) {
                if (statusEl) { statusEl.textContent = '✓ Photo updated!'; statusEl.style.color = 'var(--success)'; }
                showToast(data.message || 'Profile picture updated!', 'success');
            } else {
                if (statusEl) { statusEl.textContent = data.message || 'Upload failed.'; statusEl.style.color = 'var(--danger)'; }
            }
        } catch (err) {
            console.error('Picture upload error:', err);
            if (statusEl) { statusEl.textContent = 'Upload error.'; statusEl.style.color = 'var(--danger)'; }
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    async function removeProfilePicture() {
        if (!confirm('Remove your profile picture?')) return;
        // We send an empty profile_picture via a small AJAX call
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value || '';
            const res = await fetch("{{ route('profile.update') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                // Note: this hits the text-profile update; a dedicated remove endpoint is simpler but we
                // keep it minimal by passing the current user name so it validates.
                body: JSON.stringify({
                    name: document.getElementById('profile_name')?.value || '',
                    _remove_picture: true,
                }),
            });
            // Refresh to apply
            if (res.ok) { location.reload(); }
        } catch (err) { console.error(err); }
    }

    /* ─── Profile text-field update ─────────────────────────── */

    async function handleProfileUpdate(e) {

        e.preventDefault();


        const form =
            e.target;


        const saveBtn =
            document.getElementById(
                'saveProfileBtn'
            );


        if (!saveBtn || !form) {
            return;
        }


        saveBtn.disabled = true;

        saveBtn.innerText =
            'Saving...';


        const formData =
            new FormData(form);


        const bodyObj = {};


        formData.forEach(
            (val, key) => {

                bodyObj[key] =
                    val;

            }
        );


        try {

            const csrfTokenElement =
                document.querySelector(
                    'meta[name="csrf-token"]'
                );


            const csrfToken =
                csrfTokenElement
                    ? csrfTokenElement.content
                    : form.querySelector(
                        'input[name="_token"]'
                    )?.value;


            if (!csrfToken) {

                throw new Error(
                    'CSRF token not found.'
                );

            }


            const res =
                await fetch(
                    "{{ route('profile.update') }}",
                    {
                        method: 'POST',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'
                        },

                        body:
                            JSON.stringify(bodyObj)
                    }
                );


            const data =
                await res
                    .json()
                    .catch(() => ({}));


            if (res.ok) {

                closeModal(
                    'workerProfileModal'
                );


                showToast(
                    data.message ||
                    'Profile updated successfully!',
                    'success'
                );


                if (
                    data.user &&
                    data.user.name
                ) {

                    const userNameElement =
                        document.getElementById(
                            'userNameDisplay'
                        );


                    if (userNameElement) {

                        userNameElement.innerText =
                            data.user.name;

                    }

                }


            } else {

                alert(
                    data.message ||
                    'Failed to update profile.'
                );

            }


        } catch (err) {

            console.error(
                'Profile update error:',
                err
            );


            alert(
                'Error updating profile.'
            );


        } finally {

            saveBtn.disabled =
                false;

            saveBtn.innerText =
                'Save Profile Changes';

        }

    }


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE DASHBOARD
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'DOMContentLoaded',
        () => {

            updateLoanCalculations();

        }
    );

</script>

@endpush