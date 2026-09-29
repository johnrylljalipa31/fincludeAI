@extends('layouts.app')

@section('title', 'Data Privacy & Consent — Finclude-AI')

@section('header')
<header class="app-header">
    <a href="{{ route('dashboard') }}" class="icon-btn" title="Back to Dashboard">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div style="font-weight: 700; font-size: 1rem;">Data Privacy & Consent</div>
    <div class="header-actions">
        <a href="{{ route('privacy') }}" class="icon-btn" title="View Full Privacy Policy">
            <i class="fa-regular fa-file-lines"></i>
        </a>
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
    </div>
</header>
@endsection

@section('content')
<main class="app-body">
    <!-- Header Card -->
    <div class="f-card glow-primary animate-fade-in" style="display: flex; align-items: center; gap: 16px; padding: 18px;">
        <img src="{{ asset('images/finclude_logo.png') }}" alt="Logo" style="width: 44px; height: 44px; border-radius: 12px; object-fit: cover;">
        <div>
            <h1 class="title-md" style="font-size: 1.05rem;">RA 10173 Compliance</h1>
            <p class="text-subtext" style="font-size: 0.78rem;">Philippine Data Privacy Act of 2012 Statutory Authorization</p>
        </div>
    </div>

    <!-- Statutory Explanatory Card -->
    <div class="f-card animate-fade-in">
        <h2 class="title-md" style="margin-bottom: 12px;">Purpose of Financial Data Processing</h2>
        <p class="text-subtext" style="margin-bottom: 16px; line-height: 1.5;">
            To provide fair and affordable micro-credit without requiring traditional bank collateral or credit bureau history, Finclude-AI analyzes your alternative financial trajectory (e.g. gig platform payouts, utility payment consistency, and wallet cash flows).
        </p>

        <!-- Scopes Checklist -->
        <div class="text-caption" style="margin-bottom: 10px;">Authorized Data Scopes:</div>
        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
            <div class="f-card-sub" style="display: flex; align-items: flex-start; gap: 12px; padding: 12px;">
                <i class="fa-solid fa-building-columns" style="color: var(--primary); margin-top: 2px;"></i>
                <div>
                    <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">Open Banking Income Verification</div>
                    <div class="text-subtext" style="font-size: 0.78rem;">Read-only transaction history from connected Philippine digital wallets and banks.</div>
                </div>
            </div>

            <div class="f-card-sub" style="display: flex; align-items: flex-start; gap: 12px; padding: 12px;">
                <i class="fa-solid fa-receipt" style="color: var(--success); margin-top: 2px;"></i>
                <div>
                    <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">Utility & Telco Payment Consistency</div>
                    <div class="text-subtext" style="font-size: 0.78rem;">On-time bill settlements (electricity, water, telco) as positive credit signals.</div>
                </div>
            </div>

            <div class="f-card-sub" style="display: flex; align-items: flex-start; gap: 12px; padding: 12px;">
                <i class="fa-solid fa-robot" style="color: var(--warning); margin-top: 2px;"></i>
                <div>
                    <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">Explainable AI (SHAP) Decisioning</div>
                    <div class="text-subtext" style="font-size: 0.78rem;">Transparent scoring algorithm explaining exactly why your credit ceiling was approved.</div>
                </div>
            </div>
        </div>

        <!-- Consent Checkboxes Form -->
        <form action="{{ route('consent.submit') }}" method="POST" id="consentForm">
            @csrf

            <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px; padding: 14px; background: var(--card-sub-bg); border-radius: 14px; border: 1px solid var(--border-color);">
                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                    <input type="checkbox" id="agree_terms" required style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary);">
                    <span style="font-size: 0.85rem; color: var(--text-main); line-height: 1.4;">
                        I freely grant specific and informed consent to the processing of my alternative financial indicators in compliance with <strong>Republic Act No. 10173 (Data Privacy Act of 2012)</strong>.
                    </span>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer;">
                    <input type="checkbox" id="agree_banking" required style="width: 20px; height: 20px; margin-top: 2px; accent-color: var(--primary);">
                    <span style="font-size: 0.85rem; color: var(--text-main); line-height: 1.4;">
                        I authorize Finclude-AI to link my declared BSP-regulated electronic wallets or gig platform payout accounts via read-only Open Banking APIs.
                    </span>
                </label>
            </div>

            <button type="submit" class="btn-primary" style="padding: 15px;">
                <i class="fa-solid fa-shield-halved"></i>
                <span>I Agree & Authorize</span>
            </button>
        </form>

        <div style="text-align: center; margin-top: 18px;">
            <a href="{{ route('privacy') }}" style="color: var(--text-muted); font-size: 0.82rem; text-decoration: underline;">
                Read complete Data Subject Rights & Privacy Terms
            </a>
        </div>
    </div>
</main>
@endsection
