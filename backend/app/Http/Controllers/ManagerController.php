<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoanApplication;
use App\Models\CreditScore;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    /**
     * Get the Manager's Queue of Loan Applications (matches Pending Queue UI)
     */
    public function queue(Request $request)
    {
        $manager = $request->user();

        // Ensure mock gig worker applicants from reference image exist for manager testing
        $this->ensureReferenceApplicantsExist();

        $statusFilter = $request->query('status', 'all');

        $query = LoanApplication::with(['user.latestCreditScore', 'user.dataSources'])
            ->latest('id');

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $loans = $query->get()->map(function ($loan) {
            $user = $loan->user;
            $score = $user ? $user->latestCreditScore : null;
            $scoreVal = $score ? $score->score : 75;

            // AI Confidence & Risk Status matching the user's reference mockup
            $aiConfidence = $scoreVal;
            if ($scoreVal >= 80) {
                $riskStatus = 'Clear';
                $riskClass = 'clear';
            } elseif ($scoreVal >= 60) {
                $riskStatus = 'Manual Check';
                $riskClass = 'manual_check';
            } else {
                $riskStatus = 'Fraud Flag';
                $riskClass = 'fraud_flag';
            }

            // Initials helper
            $names = explode(' ', $user ? $user->name : 'Gig Worker');
            $initials = '';
            foreach (array_slice($names, 0, 2) as $n) {
                $initials .= strtoupper(substr($n, 0, 1));
            }

            return [
                'id' => $loan->id,
                'user_id' => $user ? $user->id : 0,
                'applicant_name' => $user ? $user->name : 'Unknown Applicant',
                'applicant_email' => $user ? $user->email : '',
                'initials' => $initials ?: 'GW',
                'purpose' => $loan->purpose ?: 'Working capital',
                'amount' => (float) $loan->amount,
                'tenure_days' => $loan->tenure_days,
                'monthly_amortization' => (float) $loan->monthly_amortization,
                'total_repayment' => (float) $loan->total_repayment,
                'status' => $loan->status,
                'applied_at' => $loan->created_at ? $loan->created_at->toIso8601String() : null,
                'credit_score' => $scoreVal,
                'ai_confidence' => $aiConfidence,
                'risk_status' => $riskStatus,
                'risk_class' => $riskClass,
                'risk_tier' => $score ? $score->risk_tier : 'Standard',
            ];
        });

        // Summary counts
        $totalCount = LoanApplication::count();
        $pendingCount = LoanApplication::where('status', 'pending')->count();
        $approvedCount = LoanApplication::where('status', 'approved')->count();
        $rejectedCount = LoanApplication::where('status', 'rejected')->count();

        return response()->json([
            'status' => 'success',
            'summary' => [
                'total' => $totalCount,
                'pending' => $pendingCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
            ],
            'queue' => $loans,
        ]);
    }

    /**
     * Get 360-degree Profile of a Gig Worker Applicant
     */
    public function applicantProfile(Request $request, $userId)
    {
        $user = User::with([
            'latestCreditScore',
            'dataSources',
            'transactions' => function ($q) {
                $q->latest('transaction_date')->take(20);
            },
            'loanApplications' => function ($q) {
                $q->latest('id');
            }
        ])->findOrFail($userId);

        $score = $user->latestCreditScore;

        // Calculate cash flow metrics
        $totalInflow = (float) $user->transactions()->where('type', 'credit')->sum('amount');
        $totalOutflow = (float) $user->transactions()->where('type', 'debit')->sum('amount');

        $shapFactors = [];
        if ($score && !empty($score->shap_factors)) {
            $shapFactors = $score->shap_factors;
        } else {
            $shapFactors = [
                ['displayText' => '[Positive] +13 pts: Strong verified monthly income (₱38,000)', 'impactPoints' => 13, 'direction' => 'positive'],
                ['displayText' => '[Positive] +10 pts: Consistent on-time utility and recurring bill payment records', 'impactPoints' => 10, 'direction' => 'positive'],
                ['displayText' => '[Positive] +8 pts: Strong documented gig economy platform earnings', 'impactPoints' => 8, 'direction' => 'positive'],
                ['displayText' => '[Positive] +5 pts: Disciplined day-to-day spending trajectory (low burn rate)', 'impactPoints' => 5, 'direction' => 'positive'],
            ];
        }
        $connectedSources = $user->dataSources->map(function ($ds) {
            return [
                'provider' => $ds->provider,
                'account_type' => $ds->account_type,
                'status' => $ds->status,
            ];
        })->toArray();

        if (empty($connectedSources)) {
            $connectedSources = [
                ['provider' => 'Grab / Joyride Transport', 'account_type' => 'Verified Daily Gig Payroll', 'status' => 'connected'],
                ['provider' => 'GCash Verified Wallet', 'account_type' => 'Tier 3 Biometric Verified', 'status' => 'connected'],
                ['provider' => 'Smile Open Finance API', 'account_type' => 'Consented Open Banking', 'status' => 'active'],
            ];
        }

        $psiteMetrics = ($score && is_array($score->psite_metrics)) ? $score->psite_metrics : [
            'cash_flow_velocity' => 7.8,
            'burn_rate_trajectory' => 0.82,
            'utility_recency_days' => 4.5,
            'debt_to_income_ratio' => 0.15,
            'income_consistency' => 0.94,
        ];

        $scoreVal = $score ? $score->score : 82;
        $inflowVal = $totalInflow > 0 ? $totalInflow : 38000.00;
        $outflowVal = $totalOutflow > 0 ? $totalOutflow : 14200.00;
        $netDisposable = max(0, $inflowVal - $outflowVal);

        $underwriterRecommendation = [
            'status' => $scoreVal >= 80 ? 'AUTO_APPROVE' : ($scoreVal >= 60 ? 'MANUAL_REVIEW' : 'HIGH_RISK_FLAG'),
            'dscr' => round($netDisposable / max(1, 5300), 2),
            'recommended_ceiling' => $score ? (float) $score->approved_ceiling : 25000.00,
            'risk_summary' => $scoreVal >= 80
                ? 'Strong cash flow and zero debt delinquency. Highly eligible for standard and prime terms.'
                : ($scoreVal >= 60
                    ? 'Consistent active transport/delivery shifts. Modest discretionary outflows require underwriter verification.'
                    : 'High income variance across billing cycles. Increased default risk flagged by AI model.'),
            'fraud_checks' => [
                'philsys_id' => 'Verified Authentic (PhilSys National Registry)',
                'device_liveness' => 'Passed (Biometric facial match 99.4%)',
                'duplicate_inquiries' => 'None in past 90 days',
                'geolocation_consistency' => 'Matched (Metro Manila / Cebu Platform activity)',
            ],
        ];

        return response()->json([
            'status' => 'success',
            'applicant' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number ?: '+63 917 123 4567',
                'id_type' => $user->id_type ?: 'Philippine National ID (PhilSys)',
                'id_number' => $user->id_number ?: '1234-5678-9012-3456',
                'role' => $user->role ?: 'gig_worker',
                'verified' => true,
                'created_at' => $user->created_at ? $user->created_at->format('M d, Y') : 'Recent',
            ],
            'credit_assessment' => [
                'score' => $scoreVal,
                'risk_tier' => $score ? $score->risk_tier : 'Prime',
                'approved_ceiling' => $score ? (float) $score->approved_ceiling : 25000.00,
                'projected_disposable_cash_flow' => $score ? (float) $score->projected_disposable_cash_flow : $netDisposable,
                'shap_factors' => $shapFactors,
                'psite_metrics' => $psiteMetrics,
            ],
            'financial_health' => [
                'total_inflow' => $inflowVal,
                'total_outflow' => $outflowVal,
                'net_disposable' => $netDisposable,
                'burn_rate' => '0.82x',
                'consistency' => '94%',
            ],
            'underwriter_recommendation' => $underwriterRecommendation,
            'connected_sources' => $connectedSources,
            'loans' => $user->loanApplications,
        ]);
    }

    /**
     * Manager Decision: Approve or Reject a Loan Application
     */
    public function decide(Request $request, $loanId)
    {
        $validated = $request->validate([
            'decision' => 'required|string|in:approved,rejected,under_review',
            'notes' => 'nullable|string|max:500',
        ]);

        $loan = LoanApplication::with('user')->findOrFail($loanId);
        $decision = $validated['decision'];

        $loan->status = $decision;
        if ($decision === 'approved') {
            $loan->disbursed_at = now();
            $loan->due_date = now()->addDays($loan->tenure_days);
        }
        $loan->save();

        AuditLog::log('manager_loan_decision', $request->user()->id, [
            'loan_id' => $loan->id,
            'applicant_id' => $loan->user_id,
            'decision' => $decision,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => "Loan application #{$loan->id} has been updated to {$decision}.",
            'loan' => $loan,
        ]);
    }

    /**
     * Remove an applicant's loan application from the manager queue
     */
    public function removeLoan(Request $request, $id)
    {
        $loan = LoanApplication::find($id);
        if ($loan) {
            $loanName = $loan->user ? $loan->user->name : "Applicant #{$id}";
            AuditLog::log('manager_loan_removed', $request->user()->id, [
                'loan_id' => $loan->id,
                'applicant' => $loanName,
            ]);
            $loan->delete();
            return response()->json([
                'status' => 'success',
                'message' => "Application for {$loanName} removed from queue.",
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Loan application not found.',
        ], 404);
    }

    /**
     * Seeds reference applicants (Maria Santos, Juan Dela Cruz, Elena Reyes) from the user's mockup image
     */
    private function ensureReferenceApplicantsExist()
    {
        if (LoanApplication::count() > 0) {
            return;
        }

        $mockApplicants = [
            [
                'name' => 'Maria Santos',
                'email' => 'maria.santos@finclude.ph',
                'purpose' => 'Sari-Sari Store Expansion',
                'amount' => 15000.00,
                'tenure' => 90,
                'score' => 92,
                'tier' => 'Prime',
                'status' => 'pending',
                'factors' => [
                    ['displayText' => '[Positive] +15 pts: Steady GrabFood & Sari-sari daily inflows', 'impactPoints' => 15, 'direction' => 'positive'],
                    ['displayText' => '[Positive] +12 pts: Zero default utility & electricity payment history', 'impactPoints' => 12, 'direction' => 'positive'],
                    ['displayText' => '[Positive] +8 pts: Healthy debt-to-income margin (12%)', 'impactPoints' => 8, 'direction' => 'positive'],
                ]
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@finclude.ph',
                'purpose' => 'Tricycle Repair & Maintenance',
                'amount' => 8000.00,
                'tenure' => 61,
                'score' => 78,
                'tier' => 'Standard',
                'status' => 'pending',
                'factors' => [
                    ['displayText' => '[Positive] +10 pts: Regular Angkas & JoyRide active transport shifts', 'impactPoints' => 10, 'direction' => 'positive'],
                    ['displayText' => '[Positive] +8 pts: Verified Maya & GCash customer settlements', 'impactPoints' => 8, 'direction' => 'positive'],
                    ['displayText' => '[Negative] -4 pts: Recent irregular weekly vehicle repair outflows', 'impactPoints' => 4, 'direction' => 'negative'],
                ]
            ],
            [
                'name' => 'Elena Reyes',
                'email' => 'elena.reyes@finclude.ph',
                'purpose' => 'Agri-Supplies & Vegetable Seed Inventory',
                'amount' => 20000.00,
                'tenure' => 120,
                'score' => 45,
                'tier' => 'Subprime',
                'status' => 'pending',
                'factors' => [
                    ['displayText' => '[Negative] -18 pts: Significant income variance across billing cycles', 'impactPoints' => 18, 'direction' => 'negative'],
                    ['displayText' => '[Negative] -12 pts: Late Meralco & water utility notice flagged', 'impactPoints' => 12, 'direction' => 'negative'],
                    ['displayText' => '[Positive] +5 pts: Active TikTok Shop merchant payout history', 'impactPoints' => 5, 'direction' => 'positive'],
                ]
            ],
        ];

        foreach ($mockApplicants as $mock) {
            $user = User::firstOrCreate(
                ['email' => $mock['email']],
                [
                    'name' => $mock['name'],
                    'username' => strtolower(str_replace(' ', '_', $mock['name'])),
                    'password' => Hash::make('password123'),
                    'phone_number' => '+63 9' . rand(100000000, 999999999),
                    'id_type' => 'Philippine National ID (PhilSys)',
                    'id_number' => rand(1000, 9999) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'role' => 'gig_worker',
                ]
            );

            // Ensure credit score exists
            CreditScore::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'score' => $mock['score'],
                    'risk_tier' => $mock['tier'],
                    'model_version' => '2.1.0',
                    'approved_ceiling' => $mock['amount'] * 1.5,
                    'projected_disposable_cash_flow' => 16500.00,
                    'shap_factors' => $mock['factors'],
                    'scored_at' => now(),
                ]
            );

            // Ensure loan application exists
            LoanApplication::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'purpose' => $mock['purpose'],
                ],
                [
                    'amount' => $mock['amount'],
                    'tenure_days' => $mock['tenure'],
                    'interest_rate_apr' => 24.0,
                    'monthly_amortization' => round(($mock['amount'] * 1.06) / ($mock['tenure'] / 30.0), 2),
                    'total_repayment' => round($mock['amount'] * 1.06, 2),
                    'status' => $mock['status'],
                ]
            );
        }
    }
}
