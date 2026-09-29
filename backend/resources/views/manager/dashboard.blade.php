@extends('layouts.app')

@section('title', 'Manager Portal — Finclude-AI')
@section('container-class', 'wide')

@section('header')
<header class="app-header">
    <div class="brand-title">
        <img src="{{ asset('images/finclude_logo.png') }}" alt="Logo" class="brand-logo-img">
        <div>
            <div style="line-height: 1.1;">Manager Portal</div>
        </div>
    </div>
    <div class="header-actions">
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Switch Theme">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
        <button type="button" class="icon-btn" onclick="location.reload()" title="Refresh Queue">
            <i class="fa-solid fa-arrows-rotate"></i>
        </button>
        <a href="{{ route('manager.profile') }}" class="icon-btn" title="Manager Profile & Logs">
            <i class="fa-regular fa-user-circle"></i>
        </a>
        <form action="{{ route('logout') }}" method="POST" style="display: inline;">
            @csrf
            <button type="submit" class="icon-btn" title="Sign Out">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </form>
    </div>
</header>
@endsection

@section('content')
<main class="app-body">
    <!-- Stat Cards Row -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
        <div class="f-card glow-primary" style="padding: 14px 12px; text-align: center;">
            <div class="text-caption" style="font-size: 0.68rem;">Total In Queue</div>
            <div class="title-lg" style="font-size: 1.5rem; margin-top: 4px; color: var(--primary);">
                {{ $counts['total'] }}
            </div>
        </div>

        <div class="f-card glow-warning" style="padding: 14px 12px; text-align: center;">
            <div class="text-caption" style="font-size: 0.68rem; color: var(--warning);">Pending</div>
            <div class="title-lg" style="font-size: 1.5rem; margin-top: 4px; color: var(--warning);">
                {{ $counts['pending'] }}
            </div>
        </div>

        <div class="f-card glow-success" style="padding: 14px 12px; text-align: center;">
            <div class="text-caption" style="font-size: 0.68rem; color: var(--success);">Approved</div>
            <div class="title-lg" style="font-size: 1.5rem; margin-top: 4px; color: var(--success);">
                {{ $counts['approved'] }}
            </div>
        </div>

        <div class="f-card glow-danger" style="padding: 14px 12px; text-align: center;">
            <div class="text-caption" style="font-size: 0.68rem; color: var(--danger);">Rejected</div>
            <div class="title-lg" style="font-size: 1.5rem; margin-top: 4px; color: var(--danger);">
                {{ $counts['rejected'] }}
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="f-card animate-fade-in" style="padding: 14px;">
        <form method="GET" action="{{ route('manager.dashboard') }}" style="display: flex; gap: 8px;">
            <div class="input-wrapper" style="flex: 1;">
                <i class="fa-solid fa-magnifying-glass input-icon"></i>
                <input type="text" name="q" class="form-control" placeholder="Search applicant by name, email, or purpose..." value="{{ $search }}">
            </div>
            <input type="hidden" name="status" value="{{ $statusFilter }}">
            <button type="submit" class="btn-primary" style="width: auto; padding: 0 18px;">
                <i class="fa-solid fa-filter"></i>
            </button>
        </form>

        <!-- Status Filter Pills -->
        <div style="display: flex; gap: 6px; margin-top: 12px; overflow-x: auto;">
            <a href="{{ route('manager.dashboard', ['status' => 'all', 'q' => $search]) }}" 
               class="tenure-chip {{ $statusFilter === 'all' ? 'selected' : '' }}" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem;">
               All Applications
            </a>
            <a href="{{ route('manager.dashboard', ['status' => 'pending', 'q' => $search]) }}" 
               class="tenure-chip {{ $statusFilter === 'pending' ? 'selected' : '' }}" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem;">
               Pending ({{ $counts['pending'] }})
            </a>
            <a href="{{ route('manager.dashboard', ['status' => 'approved', 'q' => $search]) }}" 
               class="tenure-chip {{ $statusFilter === 'approved' ? 'selected' : '' }}" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem;">
               Approved ({{ $counts['approved'] }})
            </a>
            <a href="{{ route('manager.dashboard', ['status' => 'rejected', 'q' => $search]) }}" 
               class="tenure-chip {{ $statusFilter === 'rejected' ? 'selected' : '' }}" style="text-decoration: none; padding: 6px 12px; font-size: 0.78rem;">
               Rejected ({{ $counts['rejected'] }})
            </a>
        </div>
    </div>

    <!-- Loan Applications Queue List -->
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @forelse($loans as $loan)
            @php
                $user = $loan->user;
                $score = $user ? $user->latestCreditScore : null;
                $scoreVal = $score ? $score->score : 75;

                if ($scoreVal >= 80) {
                    $scoreBadge = 'badge-success';
                    $riskBadge = 'badge-success';
                    $riskText = 'Clear';
                } elseif ($scoreVal >= 60) {
                    $scoreBadge = 'badge-warning';
                    $riskBadge = 'badge-warning';
                    $riskText = 'Manual Check';
                } else {
                    $scoreBadge = 'badge-danger';
                    $riskBadge = 'badge-danger';
                    $riskText = 'Fraud Flag';
                }

                $displayStatus = ($loan->status === 'disbursed') ? 'approved' : $loan->status;
                $statusBadge = 'badge-warning';
                if ($displayStatus === 'approved') $statusBadge = 'badge-success';
                if ($displayStatus === 'rejected') $statusBadge = 'badge-danger';
            @endphp

            <div class="f-card queue-card animate-fade-in" style="cursor: pointer;" onclick="openReviewModal({{ json_encode($loan) }}, {{ json_encode($user) }}, {{ json_encode($score) }})">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                    <div class="applicant-row">
                        <div class="applicant-avatar">
                            {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 1rem; color: var(--text-main);">
                                {{ $user->name ?? 'Gig Worker Partner' }}
                            </div>
                            <div class="text-subtext" style="font-size: 0.78rem;">
                                {{ $user->email ?? 'applicant@finclude.ph' }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--primary); font-weight: 600; margin-top: 2px;">
                                <i class="fa-solid fa-bullseye" style="font-size: 0.7rem; margin-right: 4px;"></i> {{ $loan->purpose }}
                            </div>
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div style="font-weight: 800; font-size: 1.25rem; color: var(--text-main);">
                            ₱{{ number_format($loan->amount, 2) }}
                        </div>
                        <div class="text-subtext" style="font-size: 0.72rem;">
                            {{ $loan->tenure_days }} Days • 24% APR
                        </div>
                    </div>
                </div>

                <div style="border-top: 1px solid var(--border-color); padding-top: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <span class="badge {{ $scoreBadge }}">
                            AI Score: {{ $scoreVal }}/100
                        </span>
                        <span class="badge {{ $riskBadge }}">
                            {{ $riskText }}
                        </span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="badge {{ $statusBadge }}">
                            {{ strtoupper($displayStatus) }}
                        </span>
                        <button type="button" class="btn-primary" style="padding: 6px 12px; font-size: 0.78rem; width: auto;">
                            Review <i class="fa-solid fa-chevron-right" style="font-size: 0.65rem; margin-left: 2px;"></i>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="f-card animate-fade-in" style="text-align: center; padding: 36px 20px;">
                <i class="fa-solid fa-inbox" style="font-size: 2.4rem; color: var(--text-muted); margin-bottom: 12px;"></i>
                <h3 class="title-md">No Applications Found</h3>
                <p class="text-subtext" style="font-size: 0.82rem; margin-top: 4px;">
                    There are no loan applications matching your active filter criteria.
                </p>
            </div>
        @endforelse
    </div>
</main>

<!-- Interactive Review & Underwriting Modal -->
<div id="reviewModal" class="modal-overlay">
    <div class="modal-sheet" style="max-width: 580px;">
        <div class="modal-header">
            <div>
                <h3 class="title-md" id="reviewModalTitle">Review Loan Application</h3>
                <p class="text-subtext" id="reviewModalSubtitle" style="font-size: 0.78rem;">Applicant Dossier & XAI Explainability</p>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('reviewModal')">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div id="reviewModalBody" style="display: flex; flex-direction: column; gap: 14px;">
            <!-- Content dynamically populated via JS -->
        </div>

        <!-- Manager Decision Action Buttons -->
        <div style="border-top: 1px solid var(--border-color); margin-top: 18px; padding-top: 16px; display: flex; flex-direction: column; gap: 10px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button type="button" class="btn-primary" style="background: var(--success); color: #0B1325;" onclick="makeDecision('approved')">
                    <i class="fa-solid fa-check"></i> Approve Loan
                </button>
                <button type="button" class="btn-primary" style="background: var(--danger); color: #FFFFFF;" onclick="makeDecision('rejected')">
                    <i class="fa-solid fa-xmark"></i> Reject Loan
                </button>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                <button type="button" class="btn-secondary" style="font-size: 0.8rem;" onclick="makeDecision('manual_check')">
                    <i class="fa-solid fa-flag"></i> Flag for Verification
                </button>
                <button type="button" class="btn-secondary" style="font-size: 0.8rem; color: var(--danger); border-color: rgba(239, 68, 68, 0.4);" onclick="removeLoanFromQueue()">
                    <i class="fa-solid fa-trash"></i> Remove from Queue
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let currentLoan = null;

    function openReviewModal(loan, user, score) {
        currentLoan = loan;
        document.getElementById('reviewModalTitle').innerText = `${user ? user.name : 'Applicant'} — ₱${parseFloat(loan.amount).toLocaleString('en-PH', {minimumFractionDigits: 2})}`;
        document.getElementById('reviewModalSubtitle').innerText = `ID: #${loan.id} • Purpose: ${loan.purpose}`;

        const scoreVal = score ? score.score : 75;
        const tier = score ? score.risk_tier : 'Standard';
        const factors = (score && score.shap_factors) ? score.shap_factors : [];

        let factorsHtml = '';
        factors.forEach(f => {
            const isPos = (f.direction === 'positive');
            factorsHtml += `
                <div class="shap-factor-row ${isPos ? 'positive' : 'negative'}" style="margin-bottom: 6px;">
                    <div class="shap-header">
                        <span class="shap-title" style="font-size: 0.8rem;">
                            <i class="fa-solid ${isPos ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down'}" style="color: ${isPos ? 'var(--success)' : 'var(--danger)'}"></i>
                            ${f.displayText}
                        </span>
                        <span class="shap-badge ${isPos ? 'positive' : 'negative'}">${isPos ? '+' : '-'}${Math.abs(f.impactPoints || 0)} pts</span>
                    </div>
                </div>
            `;
        });

        const bodyHtml = `
            <div class="f-card-sub" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                <div>
                    <div class="text-caption">AI Score</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: ${scoreVal >= 80 ? 'var(--success)' : (scoreVal >= 60 ? 'var(--warning)' : 'var(--danger)')}">
                        ${scoreVal}/100
                    </div>
                    <span class="badge ${scoreVal >= 80 ? 'badge-success' : (scoreVal >= 60 ? 'badge-warning' : 'badge-danger')}" style="font-size: 0.65rem;">
                        ${tier}
                    </span>
                </div>
                <div>
                    <div class="text-caption">Tenure</div>
                    <div style="font-size: 1.3rem; font-weight: 800; color: var(--text-main);">
                        ${loan.tenure_days}d
                    </div>
                    <span class="text-subtext" style="font-size: 0.7rem;">61-180d Compliant</span>
                </div>
                <div>
                    <div class="text-caption">Monthly Due</div>
                    <div style="font-size: 1.15rem; font-weight: 800; color: var(--primary);">
                        ₱${parseFloat(loan.monthly_amortization || (loan.amount / (loan.tenure_days/30))).toLocaleString('en-PH', {minimumFractionDigits: 2})}
                    </div>
                    <span class="text-subtext" style="font-size: 0.7rem;">24% Fixed APR</span>
                </div>
            </div>

            <div>
                <div class="form-label" style="margin-bottom: 6px;">SHAP Factor Attribution Signals</div>
                ${factorsHtml || '<div class="text-subtext" style="font-size: 0.8rem;">Standard gig economy verified cash flows.</div>'}
            </div>

            <div class="f-card-sub" style="font-size: 0.82rem;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--text-muted);">Verified Gov ID:</span>
                    <span style="font-weight: 600;">${user ? (user.id_type || 'PhilSys National ID') : 'N/A'}</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span style="color: var(--text-muted);">ID Number:</span>
                    <span style="font-weight: 600;">${user ? (user.id_number || 'N/A') : 'N/A'}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span style="color: var(--text-muted);">Application Status:</span>
                    <span class="badge ${loan.status === 'approved' ? 'badge-success' : 'badge-warning'}">${loan.status.toUpperCase()}</span>
                </div>
            </div>
        `;

        document.getElementById('reviewModalBody').innerHTML = bodyHtml;
        openModal('reviewModal');
    }

    async function makeDecision(decision) {
        if (!currentLoan) return;

        try {
            const res = await fetch(`/manager/loan/${currentLoan.id}/decision`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ decision: decision, notes: `Underwriter decision: ${decision}` })
            });

            const data = await res.json();
            if (res.ok) {
                closeModal('reviewModal');
                showToast(data.message || `Application marked as ${decision.toUpperCase()}`, 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                alert(data.message || 'Failed to update loan.');
            }
        } catch (err) {
            alert('Network error making decision.');
        }
    }

    async function removeLoanFromQueue() {
        if (!currentLoan) return;
        if (!confirm('Are you sure you want to remove this loan application from the queue?')) return;

        try {
            const res = await fetch(`/manager/loan/${currentLoan.id}/remove`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await res.json();
            if (res.ok) {
                closeModal('reviewModal');
                showToast(data.message || 'Application removed from queue.', 'success');
                setTimeout(() => location.reload(), 900);
            } else {
                alert(data.message || 'Failed to remove loan.');
            }
        } catch (err) {
            alert('Network error removing loan.');
        }
    }
</script>
@endpush
