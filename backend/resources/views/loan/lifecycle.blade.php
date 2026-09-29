@extends('layouts.app')

@section('title', 'Loan Lifecycle & Repayment — Finclude-AI')

@section('header')
<header class="app-header">
    <a href="{{ route('dashboard') }}" class="icon-btn" title="Back to Dashboard">
        <i class="fa-solid fa-arrow-left"></i>
    </a>
    <div style="font-weight: 700; font-size: 1rem;">Loan Lifecycle & Repayment</div>
    <div class="header-actions">
        <button type="button" class="icon-btn" onclick="toggleTheme()" title="Toggle Dark/Light Mode">
            <i class="fa-solid fa-sun theme-toggle-icon" style="color: var(--primary);"></i>
        </button>
    </div>
</header>
@endsection

@section('content')
<main class="app-body">
    <!-- Current Finclude Wallet Balance Summary Bar -->
    <div class="f-card-sub animate-fade-in" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; margin-bottom: 14px; border-radius: 12px; background: rgba(0, 229, 255, 0.05); border: 1px solid rgba(0, 229, 255, 0.2);">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(0, 229, 255, 0.15); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <div class="text-caption" style="font-size: 0.72rem; color: var(--text-muted);">Current Finclude Wallet Balance</div>
                <div style="font-weight: 800; font-size: 1.05rem; color: var(--primary);">₱{{ number_format($walletBalance, 2) }}</div>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="text-subtext" style="font-size: 0.78rem; text-decoration: none; display: flex; align-items: center; gap: 4px; color: var(--primary); font-weight: 600;">
            <span>Dashboard</span>
            <i class="fa-solid fa-chevron-right" style="font-size: 0.7rem;"></i>
        </a>
    </div>

    @if($loan)
        <!-- Current Loan Card -->
        <div class="f-card glow-primary animate-fade-in">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                <span class="text-caption" style="color: var(--primary); font-weight: 700;">
                    @if($loan->status === 'repaid')
                        Settled Micro-Loan #{{ $loan->id }}
                    @elseif($loan->status === 'rejected')
                        Micro-Loan Application #{{ $loan->id }}
                    @elseif($loan->status === 'pending')
                        Pending Application #{{ $loan->id }}
                    @elseif($loan->status === 'approved')
                        Approved Micro-Loan #{{ $loan->id }}
                    @elseif($loan->status === 'disbursed')
                        Active Micro-Loan #{{ $loan->id }}
                    @else
                        Micro-Loan #{{ $loan->id }}
                    @endif
                </span>
                @php
                    $badgeClass = 'badge-warning';
                    if ($loan->status === 'approved' || $loan->status === 'disbursed') $badgeClass = 'badge-success';
                    elseif ($loan->status === 'rejected') $badgeClass = 'badge-danger';
                    elseif ($loan->status === 'repaid') $badgeClass = 'badge-primary';
                @endphp
                <span class="badge {{ $badgeClass }}">{{ strtoupper(str_replace('_', ' ', $loan->status)) }}</span>
            </div>

            <!-- Remaining Repayment Balance Display -->
            <div class="title-xl" style="font-size: 2.2rem; margin-bottom: 2px;">
                ₱{{ number_format($remainingBalance, 2) }}
            </div>
            <div class="text-subtext" style="font-size: 0.8rem; margin-bottom: 14px;">
                {{ $loan->status === 'repaid' ? 'Micro-Loan Fully Repaid & Settled' : 'Current Remaining Repayment Balance' }}
            </div>

            @if($loan->purpose)
                <div style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-tag" style="color: var(--primary); font-size: 0.72rem;"></i>
                    <span>Purpose: <strong style="color: var(--text-main);">{{ $loan->purpose }}</strong></span>
                </div>
            @endif

            <!-- Detailed Financial Information Grid -->
            <div style="border-top: 1px solid var(--border-color); padding-top: 14px; display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px 14px; margin-bottom: 14px;">
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Loan Amount (Principal)</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                        ₱{{ number_format($loan->amount, 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Interest Rate (Fixed)</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary);">
                        {{ number_format($loan->interest_rate_apr ?? 24.0, 1) }}% APR
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Total Interest Amount</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                        ₱{{ number_format($interestAmount, 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Original Total Repayment</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                        ₱{{ number_format($originalTotalRepayment, 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Loan Tenure</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--text-main);">
                        {{ $loan->tenure_days }} Days
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Monthly Amortization</div>
                    <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary);">
                        ₱{{ number_format($perMonth, 2) }}
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Application Date</div>
                    <div style="font-weight: 700; font-size: 0.90rem; color: var(--text-main);">
                        {{ $applicationDate ? $applicationDate->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
                <div>
                    <div class="text-subtext" style="font-size: 0.72rem;">Maturity Due Date</div>
                    <div style="font-weight: 700; font-size: 0.90rem; color: var(--text-main);">
                        {{ $dueDate ? $dueDate->format('M d, Y') : 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Action & Status Handling -->
            @if($loan->status === 'disbursed' || $loan->status === 'approved')
                @if($remainingBalance > 0)
                    <button type="button" class="btn-primary" style="margin-top: 10px; padding: 14px;" onclick="openModal('repaymentModal')">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span>Make Repayment / Settle Loan</span>
                    </button>
                @else
                    <div class="f-card-sub" style="margin-top: 12px; font-size: 0.82rem; color: var(--success); display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-circle-check"></i>
                        <span>This micro-loan has been fully settled and repaid!</span>
                    </div>
                @endif
            @elseif($loan->status === 'pending')
                <div class="f-card-sub" style="margin-top: 12px; font-size: 0.82rem; color: var(--warning); display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-hourglass-half" style="font-size: 1.1rem;"></i>
                    <div>
                        <div style="font-weight: 700;">Application is Pending Review</div>
                        <div class="text-subtext" style="font-size: 0.74rem;">An underwriter is currently reviewing your loan request submitted on {{ $applicationDate ? $applicationDate->format('F d, Y') : '' }}.</div>
                    </div>
                </div>
            @elseif($loan->status === 'rejected')
                <div class="f-card-sub" style="margin-top: 12px; font-size: 0.82rem; color: var(--danger); display: flex; align-items: flex-start; gap: 10px; border-color: rgba(255, 77, 77, 0.3);">
                    <i class="fa-solid fa-circle-xmark" style="font-size: 1.1rem; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 700;">Loan Application Rejected</div>
                        <div class="text-subtext" style="font-size: 0.75rem; margin-top: 2px;">
                            {{ $loan->notes ? $loan->notes : 'Your micro-loan application was reviewed and could not be approved at this time.' }}
                        </div>
                        <a href="{{ route('dashboard') }}" class="btn-secondary" style="display: inline-flex; width: auto; margin-top: 8px; padding: 6px 14px; font-size: 0.78rem;">
                            Return to Dashboard
                        </a>
                    </div>
                </div>
            @elseif($loan->status === 'repaid')
                <div class="f-card-sub" style="margin-top: 12px; font-size: 0.82rem; color: var(--success); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>This loan is fully settled! Your on-time repayment was recorded in your credit score (+5 pts).</span>
                </div>
            @endif
        </div>

        <!-- Repayment Schedule Breakdown -->
        <div class="f-card animate-fade-in">
            <h2 class="title-md" style="margin-bottom: 4px;">Repayment Schedule Breakdown</h2>
            <p class="text-subtext" style="font-size: 0.78rem; margin-bottom: 16px;">
                Compliant with Google Financial Services: 61–180 day equal amortizations.
            </p>

            <div style="display: flex; flex-direction: column; gap: 10px;">
                @foreach($schedule as $item)
                    <div class="f-card-sub" style="display: flex; justify-content: space-between; align-items: center; padding: 12px 14px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(0, 229, 255, 0.15); color: var(--primary); font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                                #{{ $item['installment_number'] }}
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.88rem; color: var(--text-main);">
                                    Due in {{ $item['period'] }}
                                </div>
                                <div class="text-subtext" style="font-size: 0.75rem;">
                                    {{ $item['due_date'] }}
                                </div>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <div style="font-weight: 800; font-size: 0.95rem; color: var(--text-main);">
                                ₱{{ number_format($item['amount'], 2) }}
                            </div>
                            @php
                                $statusBadge = 'badge-warning';
                                if ($item['status'] === 'paid') $statusBadge = 'badge-success';
                                elseif ($item['status'] === 'upcoming') $statusBadge = 'badge-primary';
                                elseif ($item['status'] === 'cancelled') $statusBadge = 'badge-danger';
                            @endphp
                            <span class="badge {{ $statusBadge }}" style="font-size: 0.7rem; padding: 2px 8px;">
                                {{ ucfirst($item['status']) }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(($loan->status === 'approved' || $loan->status === 'disbursed') && $remainingBalance > 0)
        <!-- Interactive Repayment Modal Sheet -->
        <div id="repaymentModal" class="modal-overlay">
            <div class="modal-sheet">
                <div class="modal-header">
                    <div>
                        <h3 class="title-md">Make Loan Repayment</h3>
                        <p class="text-subtext" style="font-size: 0.78rem;">Remaining Balance: ₱<span id="modalRemainingDisplay">{{ number_format($remainingBalance, 2) }}</span></p>
                    </div>
                    <button type="button" class="modal-close-btn" onclick="closeModal('repaymentModal')">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <div id="modalAlertContainer" style="display: none; margin-bottom: 12px;"></div>

                <form id="repayForm" onsubmit="handleRepaymentSubmit(event)">
                    @csrf
                    <input type="hidden" name="loan_id" value="{{ $loan->id }}">

                    <div class="form-group">
                        <label class="form-label">Payment Amount</label>
                        <div style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px;">
                            <label class="f-card-sub" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; cursor: pointer;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="radio" name="amount_option" id="opt_installment" value="installment" checked onchange="setRepayAmount({{ min($perMonth, $remainingBalance) }})" style="accent-color: var(--primary);">
                                    <span style="font-size: 0.85rem; font-weight: 600;">Monthly Installment</span>
                                </div>
                                <span style="font-weight: 800; color: var(--primary);">₱{{ number_format(min($perMonth, $remainingBalance), 2) }}</span>
                            </label>

                            <label class="f-card-sub" style="display: flex; align-items: center; justify-content: space-between; padding: 10px 14px; cursor: pointer;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="radio" name="amount_option" id="opt_full" value="full" onchange="setRepayAmount({{ $remainingBalance }})" style="accent-color: var(--primary);">
                                    <span style="font-size: 0.85rem; font-weight: 600;">Full Settlement</span>
                                </div>
                                <span style="font-weight: 800; color: var(--success);">₱{{ number_format($remainingBalance, 2) }}</span>
                            </label>
                        </div>

                        <div class="input-wrapper">
                            <i class="fa-solid fa-peso-sign input-icon"></i>
                            <input type="number" step="0.01" min="0.01" max="{{ $remainingBalance }}" id="customRepayAmount" name="amount" class="form-control" value="{{ number_format(min($perMonth, $remainingBalance), 2, '.', '') }}" required oninput="handleAmountInput(this.value)">
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label class="form-label">Payment Channel</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-wallet input-icon"></i>
                            <select name="payment_method" id="repayPaymentMethod" class="form-control" required>
                                <option value="wallet">Finclude Wallet (Available: ₱{{ number_format($walletBalance, 2) }})</option>
                                <option value="gcash">GCash E-Wallet</option>
                                <option value="maya">Maya Digital Wallet</option>
                                <option value="bdo">BDO Online Transfer</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" id="repaySubmitBtn" class="btn-primary" style="padding: 14px;">
                        <span id="repayBtnText">Confirm Repayment</span>
                        <i id="repayBtnSpinner" class="fa-solid fa-circle-notch fa-spin" style="display: none; margin-left: 8px;"></i>
                    </button>
                </form>
            </div>
        </div>
        @endif
    @else
        <!-- No Active Loan View -->
        <div class="f-card animate-fade-in" style="text-align: center; padding: 36px 20px;">
            <div style="width: 72px; height: 72px; border-radius: 50%; background: var(--card-sub-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 18px auto; border: 1px solid var(--border-color);">
                <i class="fa-solid fa-credit-card"></i>
            </div>
            <h2 class="title-lg" style="margin-bottom: 8px;">No Active Loan</h2>
            <p class="text-subtext" style="max-width: 320px; margin: 0 auto 24px auto;">
                You currently do not have an active micro-loan. You can request a loan from your approved credit ceiling anytime on your Dashboard.
            </p>
            <a href="{{ route('dashboard') }}" class="btn-primary" style="display: inline-flex; width: auto; padding: 14px 28px;">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Back to Dashboard</span>
            </a>
        </div>
    @endif
</main>
@endsection

@push('scripts')
<script>
    const maxRemaining = {{ isset($remainingBalance) ? $remainingBalance : 0.0 }};
    const userWalletBalance = {{ isset($walletBalance) ? $walletBalance : 0.0 }};

    function setRepayAmount(amt) {
        const val = parseFloat(amt);
        document.getElementById('customRepayAmount').value = val.toFixed(2);
        clearModalAlert();
    }

    function handleAmountInput(val) {
        const amt = parseFloat(val);
        const optInstallment = document.getElementById('opt_installment');
        const optFull = document.getElementById('opt_full');
        if (optInstallment && optFull) {
            optInstallment.checked = false;
            optFull.checked = false;
        }
        clearModalAlert();
    }

    function showModalAlert(message, type = 'danger') {
        const container = document.getElementById('modalAlertContainer');
        if (!container) return;
        container.style.display = 'block';
        container.innerHTML = `
            <div class="alert-banner alert-${type} animate-fade-in" style="margin: 0; padding: 10px 14px; font-size: 0.82rem;">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>${message}</span>
            </div>
        `;
    }

    function clearModalAlert() {
        const container = document.getElementById('modalAlertContainer');
        if (container) {
            container.style.display = 'none';
            container.innerHTML = '';
        }
    }

    async function handleRepaymentSubmit(e) {
        e.preventDefault();
        clearModalAlert();

        const form = e.target;
        const btn = document.getElementById('repaySubmitBtn');
        const text = document.getElementById('repayBtnText');
        const spin = document.getElementById('repayBtnSpinner');

        const amountInput = document.getElementById('customRepayAmount');
        const amount = parseFloat(amountInput.value);
        const paymentMethod = document.getElementById('repayPaymentMethod').value;

        // Frontend Validation
        if (isNaN(amount) || amount <= 0) {
            showModalAlert('Please enter a valid repayment amount greater than zero.');
            return;
        }

        if (amount > maxRemaining) {
            showModalAlert(`Payment amount (₱${amount.toFixed(2)}) cannot exceed remaining balance of ₱${maxRemaining.toFixed(2)}.`);
            return;
        }

        if (paymentMethod === 'wallet' && amount > userWalletBalance) {
            showModalAlert(`Insufficient Finclude Wallet balance (₱${userWalletBalance.toFixed(2)}). Please select GCash, Maya, or BDO.`);
            return;
        }

        // Loading state
        btn.disabled = true;
        text.innerText = 'Processing Repayment...';
        spin.style.display = 'inline-block';

        const payload = {
            loan_id: form.querySelector('input[name="loan_id"]').value,
            amount: amount,
            payment_method: paymentMethod,
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]') 
            ? document.querySelector('meta[name="csrf-token"]').content 
            : (form.querySelector('input[name="_token"]') ? form.querySelector('input[name="_token"]').value : '');

        try {
            const res = await fetch("{{ route('loan.repay.submit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            const data = await res.json();

            if (res.ok && data.status === 'success') {
                closeModal('repaymentModal');
                if (typeof showToast === 'function') {
                    showToast(data.message || 'Payment processed successfully!', 'success');
                } else {
                    alert(data.message || 'Payment processed successfully!');
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                let errorMsg = data.message || 'Payment failed. Please verify your details.';
                if (data.errors && typeof data.errors === 'object') {
                    const firstErr = Object.values(data.errors)[0];
                    if (Array.isArray(firstErr)) errorMsg = firstErr[0];
                }
                showModalAlert(errorMsg);
                btn.disabled = false;
                text.innerText = 'Confirm Repayment';
                spin.style.display = 'none';
            }
        } catch (err) {
            showModalAlert('Network or server error while processing loan repayment.');
            btn.disabled = false;
            text.innerText = 'Confirm Repayment';
            spin.style.display = 'none';
        }
    }
</script>
@endpush
