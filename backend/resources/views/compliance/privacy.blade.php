@extends('layouts.app')

@section('title', 'Data Privacy Policy — Finclude-AI')

@section('header')
<header class="app-header">
    <a href="javascript:history.back()" class="icon-btn" title="Back">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div style="font-weight: 700; font-size: 1rem;">Data Privacy Policy</div>
    <div class="header-actions">
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
    </div>
</header>
@endsection

@section('content')
<main class="app-body">
    <div class="f-card glow-primary animate-fade-in" style="padding: 20px;">
        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
            <div style="background: rgba(0, 229, 255, 0.15); color: var(--primary); padding: 10px; border-radius: 12px; font-size: 1.3rem;">
                <i class="fa-solid fa-scale-balanced"></i>
            </div>
            <div>
                <h1 class="title-md">Republic Act No. 10173</h1>
                <p class="text-subtext" style="font-size: 0.78rem;">Data Privacy Act of 2012 Compliance Disclosure</p>
            </div>
        </div>
        <p class="text-subtext" style="font-size: 0.85rem; line-height: 1.5;">
            Finclude-AI strictly upholds the principles of transparency, legitimate purpose, and proportionality mandated by the National Privacy Commission (NPC) of the Republic of the Philippines.
        </p>
    </div>

    <!-- Section 1 -->
    <div class="f-card animate-fade-in">
        <h2 class="title-md" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 0.95rem;">
            <i class="fa-solid fa-shield-halved" style="color: var(--primary);"></i>
            1. Purpose and Scope of Processing
        </h2>
        <p class="text-subtext" style="font-size: 0.85rem; line-height: 1.5; margin-bottom: 8px;">
            We process alternative data points solely to calculate personalized credit scores and offer non-predatory micro-loans to underserved gig workers and transport operators.
        </p>
        <ul style="padding-left: 20px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.6;">
            <li>Daily, weekly, and monthly gig platform earnings</li>
            <li>Utility bill payment consistency (e.g. Meralco, Maynilad, Manila Water, Telcos)</li>
            <li>Inflow-outflow cash trajectory and burn rates</li>
        </ul>
    </div>

    <!-- Section 2 -->
    <div class="f-card animate-fade-in">
        <h2 class="title-md" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 0.95rem;">
            <i class="fa-solid fa-lock" style="color: var(--success);"></i>
            2. Security & Encryption Standards
        </h2>
        <p class="text-subtext" style="font-size: 0.85rem; line-height: 1.5;">
            All data in transit is protected using bank-grade <strong>TLS 1.3 encryption</strong>, and stored indicators are secured using <strong>AES-256 cryptographic standards</strong>. Finclude-AI <strong>NEVER</strong> stores your online banking passwords or PINs.
        </p>
    </div>

    <!-- Section 3 -->
    <div class="f-card animate-fade-in">
        <h2 class="title-md" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 0.95rem;">
            <i class="fa-solid fa-user-check" style="color: var(--warning);"></i>
            3. Your Statutory Rights as Data Subject
        </h2>
        <div style="display: flex; flex-direction: column; gap: 8px; margin-top: 10px; font-size: 0.83rem;">
            <div class="f-card-sub" style="padding: 10px;">
                <strong>Right to be Informed:</strong> You have the right to know how your financial data is evaluated and scored.
            </div>
            <div class="f-card-sub" style="padding: 10px;">
                <strong>Right to Access:</strong> You can view all linked accounts, transactions, and score factor attributions anytime on your Dashboard.
            </div>
            <div class="f-card-sub" style="padding: 10px;">
                <strong>Right to Erasure / Blocking:</strong> You can request account disconnection and data deletion upon settlement of active loans.
            </div>
        </div>
    </div>

    <!-- Section 4 -->
    <div class="f-card animate-fade-in" style="margin-bottom: 16px;">
        <h2 class="title-md" style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: 0.95rem;">
            <i class="fa-solid fa-gavel" style="color: var(--purple);"></i>
            4. Google Financial Services Compliance
        </h2>
        <p class="text-subtext" style="font-size: 0.85rem; line-height: 1.5;">
            Finclude-AI strictly complies with the Google Play Developer Policy for Personal Loans:
        </p>
        <ul style="padding-left: 20px; font-size: 0.82rem; color: var(--text-muted); line-height: 1.6; margin-top: 8px;">
            <li>Minimum repayment tenure: <strong>61 Days</strong></li>
            <li>Maximum repayment tenure: <strong>180 Days</strong></li>
            <li>Maximum Annual Percentage Rate (APR): <strong>24.0%</strong></li>
            <li>Zero hidden balloon fees or deceptive charges</li>
        </ul>
    </div>

    <button type="button" class="btn-primary" onclick="history.back()">
        <i class="fa-solid fa-arrow-left"></i>
        <span>Return to Previous Screen</span>
    </button>
</main>
@endsection
