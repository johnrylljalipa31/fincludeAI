<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoanApplication;
use App\Models\CreditScore;
use App\Models\Consent;
use App\Models\SmileAccount;
use App\Models\Transaction;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PasswordResetCodeMail;
use Twilio\Rest\Client;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class WebController extends Controller
{
    public function __construct()
    {
        $this->ensureDemoAccountsExist();
    }

    /**
     * Landing / root redirect
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();

            if ($user->role === 'admin' || $user->user_type === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            if ($user->role === 'manager' || $user->user_type === 'manager') {
                return redirect()->route('manager.dashboard');
            }

            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }

    /**
     * Show Login View
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->index();
        }

        return view('auth.login');
    }

    /**
     * Handle Login Submission
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => 'required|string',
            'password'   => 'required|string',
        ]);

        $identifier = $credentials['identifier'];

        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'The provided credentials do not match our records.'
                ], 422);
            }

            return back()->withInput()->withErrors([
                'identifier' => 'Invalid email/username or password.'
            ]);
        }

        Auth::login($user, $request->boolean('remember', true));
        $request->session()->regenerate();

        AuditLog::log('user_login_web', $user->id, [
            'ip' => $request->ip(),
            'role' => $user->role,
        ]);

        if ($user->role === 'admin' || $user->user_type === 'admin') {
            $redirectUrl = route('admin.dashboard');
        } elseif ($user->role === 'manager' || $user->user_type === 'manager') {
            $redirectUrl = route('manager.dashboard');
        } else {
            $redirectUrl = route('dashboard');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Signed in successfully!',
                'redirect' => $redirectUrl,
            ]);
        }

        return redirect()->intended($redirectUrl);
    }

    /**
     * Show Register View
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->index();
        }

        return view('auth.register');
    }

    /**
     * Handle Registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'required|string|email|max:255|unique:users',
            'password'     => 'required|string|min:6|confirmed',
            'phone_number' => 'nullable|string|max:20',
            'id_type'      => 'nullable|string|max:100',
            'id_number'    => 'nullable|string|max:100',
        ]);

        $username = explode('@', $validated['email'])[0];

        if (User::where('username', $username)->exists()) {
            $username .= '_' . rand(100, 999);
        }

        $user = User::create([
            'name'         => $validated['name'],
            'username'     => $username,
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'id_type'      => $validated['id_type'] ?? 'Philippine National ID (PhilSys)',
            'id_number'    => $validated['id_number'] ?? null,
            'role'         => 'gig_worker',
            'user_type'    => 'gig_worker',
        ]);

        // Create default Credit Score
        CreditScore::create([
            'user_id' => $user->id,
            'score' => 78,
            'risk_tier' => 'Standard',
            'model_version' => '2.1.0',
            'approved_ceiling' => 15000.00,
            'projected_disposable_cash_flow' => 16500.00,
            'shap_factors' => [
                [
                    'displayText' => 'Initial PhilSys ID verification verified',
                    'impactPoints' => 10,
                    'direction' => 'positive'
                ],
                [
                    'displayText' => 'Profile onboarding completed',
                    'impactPoints' => 8,
                    'direction' => 'positive'
                ],
                [
                    'displayText' => 'Pending platform account linkage',
                    'impactPoints' => -4,
                    'direction' => 'negative'
                ],
            ],
            'scored_at' => now(),
        ]);

        AuditLog::log('user_registered_web', $user->id, [
            'email' => $user->email,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Account created successfully! Proceeding to Data Privacy Consent...',
                'redirect' => route('consent'),
            ]);
        }

        return redirect()->route('consent')
            ->with('success', 'Account created! Please authorize data privacy consent to unlock alternative credit limits.');
    }

    /**
     * Send Password Reset Verification Code
     *
     * Sends the same 6-digit code to the user's registered email
     * and registered mobile number when those channels are configured.
     */
    public function sendPasswordCode(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim($request->input('identifier'));

        /*
         * Search using:
         * - email
         * - username
         * - name
         * - phone_number
         */
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('name', $identifier)
            ->orWhere('phone_number', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Walang nahanap na account gamit ang Email, Username, Name, o Mobile Number na ito.'
            ], 404);
        }

        /*
         * Generate 6-digit verification code.
         */
        $code = random_int(100000, 999999);

        /*
         * Save code for 10 minutes.
         */
        $cacheKey = 'pwd_reset_' . $identifier;

        Cache::put(
            $cacheKey,
            $code,
            now()->addMinutes(10)
        );

        $emailSent = false;
        $smsSent = false;
        $sendErrors = [];

        /*
         * ---------------------------------------------------------
         * 1. SEND CODE TO REGISTERED EMAIL / GMAIL
         * ---------------------------------------------------------
         */
        if ($user->email && filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($user->email)->send(
                    new PasswordResetCodeMail($code)
                );

                $emailSent = true;
            } catch (\Throwable $e) {
                $sendErrors[] = 'email';

                Log::error('Password reset email failed.', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
         * ---------------------------------------------------------
         * 2. SEND CODE TO REGISTERED MOBILE NUMBER VIA TWILIO SMS
         * ---------------------------------------------------------
         */
        if ($user->phone_number) {
            try {
                $accountSid = config('services.twilio.sid');
                $authToken = config('services.twilio.token');
                $fromNumber = config('services.twilio.from');

                if (!$accountSid || !$authToken || !$fromNumber) {
                    throw new \RuntimeException(
                        'Twilio environment variables are not configured.'
                    );
                }

                $phoneNumber = preg_replace(
                    '/[\s\-\(\)]/',
                    '',
                    trim($user->phone_number)
                );

                // Convert common Philippine formats to E.164.
                if (str_starts_with($phoneNumber, '09')) {
                    $phoneNumber = '+63' . substr($phoneNumber, 1);
                } elseif (str_starts_with($phoneNumber, '63')) {
                    $phoneNumber = '+' . $phoneNumber;
                }

                $twilio = new Client(
                    $accountSid,
                    $authToken
                );

                $twilio->messages->create(
                    $phoneNumber,
                    [
                        'from' => $fromNumber,
                        'body' => "Finclude-AI verification code: {$code}. This code expires in 10 minutes. If you did not request this, ignore this message.",
                    ]
                );

                $smsSent = true;
            } catch (\Throwable $e) {
                $sendErrors[] = 'sms';

                Log::error('Password reset SMS failed.', [
                    'user_id' => $user->id,
                    'phone_number' => $user->phone_number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        /*
         * Do not leave a reset code in cache if neither channel worked.
         */
        if (!$emailSent && !$smsSent) {
            Cache::forget($cacheKey);

            return response()->json([
                'status' => 'error',
                'message' => 'Hindi naipadala ang verification code. Siguraduhing tama ang Gmail at SMS/Twilio configuration.'
            ], 500);
        }

        $channels = [];

        if ($emailSent) {
            $channels[] = 'email';
        }

        if ($smsSent) {
            $channels[] = 'SMS';
        }

        AuditLog::log('password_reset_code_requested', $user->id, [
            'identifier_type' => filter_var($identifier, FILTER_VALIDATE_EMAIL)
                ? 'email'
                : 'mobile_or_username',
            'channels' => $channels,
            'partial_failure' => count($sendErrors) > 0,
        ]);

        if ($emailSent && $smsSent) {
            $message = 'Naipadala na ang verification code sa iyong registered email at mobile number.';
        } elseif ($emailSent) {
            $message = 'Naipadala na ang verification code sa iyong registered email.';
        } else {
            $message = 'Naipadala na ang verification code sa iyong registered mobile number.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
        ]);
    }

    /**
     * Verify Password Reset Code and Update Password
     */
    public function verifyAndResetPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'code'       => 'required|digits:6',
            'password'   => 'required|string|min:6|confirmed',
        ]);

        $identifier = trim($request->input('identifier'));
        $code = $request->input('code');
        $cacheKey = 'pwd_reset_' . $identifier;

        /*
         * Get verification code from cache
         */
        $cachedCode = Cache::get($cacheKey);

        if (!$cachedCode || (string) $cachedCode !== (string) $code) {
            return response()->json([
                'status' => 'error',
                'message' => 'Mali o lumipas na ang verification code.'
            ], 400);
        }

        /*
         * Find user
         */
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('name', $identifier)
            ->orWhere('phone_number', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hindi nahanap ang user.'
            ], 404);
        }

        /*
         * Update password
         */
        $user->password = Hash::make($request->input('password'));
        $user->save();

        /*
         * Delete verification code
         */
        Cache::forget($cacheKey);

        AuditLog::log('password_reset_completed', $user->id, [
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Matagumpay na na-update ang iyong password! Maaari ka nang mag-login.'
        ]);
    }

    /**
     * Legacy Password Reset
     *
     * Maaari pa ring gamitin ng existing frontend
     * kung meron itong direct password reset request.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $validated['email'])
            ->orWhere('username', $validated['email'])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with this email or username.',
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        AuditLog::log('password_reset_web', $user->id, [
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully! You can now log in with your new password.',
        ]);
    }

    /**
     * Log out
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been safely signed out.');
    }

    /**
     * Worker Dashboard View
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->user_type === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->role === 'manager' || $user->user_type === 'manager') {
            return redirect()->route('manager.dashboard');
        }

        $latestScore = CreditScore::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if (!$latestScore) {
            $latestScore = CreditScore::create([
                'user_id' => $user->id,
                'score' => 82,
                'risk_tier' => 'Prime',
                'model_version' => '2.1.0',
                'approved_ceiling' => 25000.00,
                'projected_disposable_cash_flow' => 18500.00,
                'shap_factors' => [
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
                        'displayText' => 'Verified gig platform earnings',
                        'impactPoints' => 8,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' => 'Disciplined day-to-day spending trajectory',
                        'impactPoints' => 5,
                        'direction' => 'positive'
                    ],
                ],
                'scored_at' => now(),
            ]);
        }

        $activeLoan = LoanApplication::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved', 'disbursed'])
            ->latest('id')
            ->first();

        // Calculate wallet balance
        $totalCredits = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'credit')
            ->sum('amount');

        $totalDebits = (float) Transaction::where('user_id', $user->id)
            ->where('type', 'debit')
            ->sum('amount');

        $disbursedLoansTotal = (float) LoanApplication::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'disbursed'])
            ->sum('amount');

        if ($totalCredits > 0 || $totalDebits > 0 || $disbursedLoansTotal > 0) {
            $walletBalance = max(
                500.00,
                ($totalCredits + $disbursedLoansTotal) - $totalDebits
            );
        } else {
            $walletBalance = 24750.00;
            $totalCredits = 38000.00;
            $totalDebits = 13250.00;
        }

        $consent = $user->latestConsent;
        $smileAccounts = $user->smileAccounts;

        return view('dashboard.worker', compact(
            'user',
            'latestScore',
            'activeLoan',
            'walletBalance',
            'totalCredits',
            'totalDebits',
            'consent',
            'smileAccounts'
        ));
    }

    /**
     * Submit Loan Application
     */
    public function applyLoan(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'amount'      => 'required|numeric|min:1000',
            'tenure_days' => 'required|integer|in:61,90,120,180',
            'purpose'     => 'required|string|max:255',
        ]);

        // Laravel validation confirms the values are valid, but request
        // input can still arrive as strings. Cast them before doing math
        // or passing tenure_days to Carbon::addDays().
        $validated['amount'] = (float) $validated['amount'];
        $validated['tenure_days'] = (int) $validated['tenure_days'];

        $score = CreditScore::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $ceiling = $score
            ? (float) $score->approved_ceiling
            : 15000.00;

        if ($validated['amount'] > $ceiling) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Requested amount exceeds your approved credit ceiling of ₱' .
                        number_format($ceiling, 2)
                ], 422);
            }

            return back()->withErrors([
                'amount' => 'Requested amount exceeds credit ceiling of ₱' .
                    number_format($ceiling, 2)
            ]);
        }

        // 24% Fixed Compliant APR
        $apr = 24.0;

        $interest = $validated['amount']
            * ($apr / 100.0)
            * ($validated['tenure_days'] / 365.0);

        $totalRepayment = $validated['amount'] + $interest;

        $months = $validated['tenure_days'] / 30.0;

        $monthlyAmortization = $totalRepayment / $months;

        $loan = LoanApplication::create([
            'user_id'              => $user->id,
            'amount'               => $validated['amount'],
            'tenure_days'          => $validated['tenure_days'],
            'interest_rate_apr'    => $apr,
            'total_repayment'      => round($totalRepayment, 2),
            'monthly_amortization' => round($monthlyAmortization, 2),
            'purpose'              => $validated['purpose'],
            'status'               => 'pending',
            'due_date'             => now()->addDays($validated['tenure_days']),
        ]);

        AuditLog::log('loan_application_submitted', $user->id, [
            'loan_id' => $loan->id,
            'amount' => $loan->amount,
            'tenure' => $loan->tenure_days,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Micro-loan request submitted successfully! An underwriter is reviewing your application.',
                'loan' => $loan,
            ]);
        }

        return redirect()->route('loan.lifecycle')
            ->with('success', 'Loan request submitted successfully! Check your application status below.');
    }

    /**
     * Calculate user's Finclude Wallet Balance
     */
    private function calculateWalletBalance($userId): float
    {
        $totalCredits = (float) Transaction::where('user_id', $userId)
            ->where('type', 'credit')
            ->sum('amount');

        $totalDebits = (float) Transaction::where('user_id', $userId)
            ->where('type', 'debit')
            ->sum('amount');

        $disbursedLoansTotal = (float) LoanApplication::where('user_id', $userId)
            ->whereIn('status', ['approved', 'disbursed'])
            ->sum('amount');

        if ($totalCredits > 0 || $totalDebits > 0 || $disbursedLoansTotal > 0) {
            return (float) max(0.00, ($totalCredits + $disbursedLoansTotal) - $totalDebits);
        }

        return 24750.00;
    }

    /**
     * Show Loan Lifecycle & Repayment Schedule
     */
    public function loanLifecycle()
    {
        $user = Auth::user();

        $loan = LoanApplication::where('user_id', $user->id)
            ->latest('id')
            ->first();

        $walletBalance = $this->calculateWalletBalance($user->id);

        $schedule = [];
        $interestAmount = 0.00;
        $originalTotalRepayment = 0.00;
        $remainingBalance = 0.00;
        $perMonth = 0.00;
        $dueDate = null;
        $applicationDate = null;

        if ($loan) {
            $apr = (float) ($loan->interest_rate_apr ?? 24.00);
            $tenureDays = (int) ($loan->tenure_days ?? 90);
            $principal = (float) $loan->amount;

            $interestAmount = round($principal * ($apr / 100.0) * ($tenureDays / 365.0), 2);
            $originalTotalRepayment = round($principal + $interestAmount, 2);

            $remainingBalance = ($loan->status === 'repaid')
                ? 0.00
                : max(0.00, (float) $loan->total_repayment);

            $applicationDate = $loan->created_at ? Carbon::parse($loan->created_at) : now();

            $dueDate = $loan->due_date
                ? Carbon::parse($loan->due_date)
                : $applicationDate->copy()->addDays($tenureDays);

            $installmentsCount = max(2, (int) round($tenureDays / 30));

            $perMonth = $loan->monthly_amortization > 0
                ? (float) $loan->monthly_amortization
                : round($originalTotalRepayment / $installmentsCount, 2);

            $totalPaid = max(0.00, round($originalTotalRepayment - $remainingBalance, 2));

            for ($i = 1; $i <= $installmentsCount; $i++) {
                if ($loan->status === 'repaid') {
                    $itemStatus = 'paid';
                } elseif ($loan->status === 'rejected') {
                    $itemStatus = 'cancelled';
                } elseif ($loan->status === 'pending') {
                    $itemStatus = 'pending';
                } else {
                    $cumulativeTarget = min($originalTotalRepayment, round($perMonth * $i, 2));
                    if ($totalPaid >= ($cumulativeTarget - 0.05)) {
                        $itemStatus = 'paid';
                    } elseif ($i === 1 || ($totalPaid >= (round($perMonth * ($i - 1), 2) - 0.05))) {
                        $itemStatus = 'upcoming';
                    } else {
                        $itemStatus = 'scheduled';
                    }
                }

                $schedule[] = [
                    'installment_number' => $i,
                    'period' => ($i * 30) . ' days',
                    'amount' => round($perMonth, 2),
                    'due_date' => $applicationDate->copy()->addDays($i * 30)->format('M d, Y'),
                    'status' => $itemStatus,
                ];
            }
        }

        return view(
            'loan.lifecycle',
            compact(
                'user',
                'loan',
                'schedule',
                'walletBalance',
                'interestAmount',
                'originalTotalRepayment',
                'remainingBalance',
                'perMonth',
                'dueDate',
                'applicationDate'
            )
        );
    }

    /**
     * Settle / Repay Micro-Loan Installment or Balance
     */
    public function repayLoan(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'loan_id'        => 'required|exists:loan_applications,id',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:wallet,gcash,maya,bdo',
        ]);

        $loan = LoanApplication::where('id', $validated['loan_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if ($loan->status === 'repaid' || (float) $loan->total_repayment <= 0) {
            $msg = 'This micro-loan is already fully repaid and settled.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $msg
                ], 422);
            }
            return back()->withErrors(['amount' => $msg]);
        }

        if (!in_array($loan->status, ['approved', 'disbursed'])) {
            $msg = "Repayments can only be submitted for approved or disbursed loans (Current status: {$loan->status}).";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $msg
                ], 422);
            }
            return back()->withErrors(['amount' => $msg]);
        }

        $amountToPay = round((float) $validated['amount'], 2);
        $remainingRepaymentBefore = round((float) $loan->total_repayment, 2);

        // Prevent overpayment
        if ($amountToPay > $remainingRepaymentBefore) {
            $msg = 'Payment amount (₱' . number_format($amountToPay, 2) .
                ') cannot exceed the remaining balance of ₱' .
                number_format($remainingRepaymentBefore, 2) . '.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $msg
                ], 422);
            }
            return back()->withErrors(['amount' => $msg]);
        }

        // Wallet repayment balance check
        if ($validated['payment_method'] === 'wallet') {
            $currentWallet = $this->calculateWalletBalance($user->id);

            if ($currentWallet < $amountToPay) {
                $msg = 'Insufficient Finclude Wallet balance (₱' .
                    number_format($currentWallet, 2) .
                    '). Please choose GCash, Maya, or BDO.';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $msg
                    ], 422);
                }
                return back()->withErrors(['amount' => $msg]);
            }
        }

        $isFullySettled = false;
        $remainingRepayment = 0.00;

        DB::transaction(function () use (
            $user,
            $loan,
            $validated,
            $amountToPay,
            $remainingRepaymentBefore,
            &$isFullySettled,
            &$remainingRepayment
        ) {
            $methodName = ucfirst($validated['payment_method']);
            $txnId = 'REP-' . strtoupper($validated['payment_method']) . '-' . $loan->id . '-' . time() . '-' . Str::random(4);

            Transaction::create([
                'user_id' => $user->id,
                'transaction_id' => $txnId,
                'type' => 'debit',
                'amount' => $amountToPay,
                'category' => 'loan_repayment',
                'description' => "Micro-Loan Repayment for Loan #{$loan->id} via {$methodName}",
                'transaction_date' => now(),
                'raw_payload' => [
                    'loan_id' => $loan->id,
                    'payment_method' => $validated['payment_method'],
                    'amount_paid' => $amountToPay,
                    'previous_balance' => $remainingRepaymentBefore,
                    'remaining_balance' => max(0.00, round($remainingRepaymentBefore - $amountToPay, 2)),
                ],
            ]);

            $remainingRepayment = max(
                0.00,
                round($remainingRepaymentBefore - $amountToPay, 2)
            );

            $loan->total_repayment = $remainingRepayment;
            $isFullySettled = ($remainingRepayment <= 0);

            if ($isFullySettled) {
                $loan->status = 'repaid';
                $loan->repaid_at = now();

                // Reward score for on-time loan settlement (+5 pts)
                $score = CreditScore::where('user_id', $user->id)
                    ->latest('id')
                    ->first();

                if ($score) {
                    $newScore = min(99, $score->score + 5);

                    $newCeiling = min(
                        50000.00,
                        (float) $score->approved_ceiling + 5000.00
                    );

                    $factors = $score->shap_factors ?? [];

                    array_unshift($factors, [
                        'displayText' =>
                            "+5 pts: Micro-Loan #{$loan->id} successfully settled on time",
                        'impactPoints' => 5,
                        'direction' => 'positive',
                    ]);

                    $score->update([
                        'score' => $newScore,
                        'risk_tier' => (
                            $newScore >= 80
                                ? 'Prime'
                                : ($newScore >= 60 ? 'Standard' : 'Subprime')
                        ),
                        'approved_ceiling' => $newCeiling,
                        'shap_factors' => array_slice($factors, 0, 5),
                        'scored_at' => now(),
                    ]);
                }
            }

            $loan->save();

            AuditLog::log('loan_repayment_submitted', $user->id, [
                'loan_id' => $loan->id,
                'payment_method' => $validated['payment_method'],
                'amount' => $amountToPay,
                'remaining' => $remainingRepayment,
                'status' => $loan->status,
            ]);
        });

        $msg = $isFullySettled
            ? "Congratulations! Loan #{$loan->id} has been fully settled. Your credit score increased by +5 points!"
            : "Payment of ₱" .
                number_format($amountToPay, 2) .
                " received via " . ucfirst($validated['payment_method']) . ". Remaining balance: ₱" .
                number_format($remainingRepayment, 2);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => $msg,
                'remaining' => $remainingRepayment,
                'is_settled' => $isFullySettled,
                'loan' => $loan->fresh(),
            ]);
        }

        return redirect()->route('loan.lifecycle')
            ->with('success', $msg);
    }

    /**
     * Show RA 10173 Consent View
     */
    public function showConsent()
    {
        $user = Auth::user();
        $consent = $user->latestConsent;

        return view(
            'compliance.consent',
            compact('user', 'consent')
        );
    }

    /**
     * Save RA 10173 Consent
     */
    public function submitConsent(Request $request)
    {
        $user = Auth::user();

        $consent = Consent::create([
            'user_id' => $user->id,
            'has_agreed' => true,
            'policy_version' => '1.0',
            'legal_basis' => 'RA 10173 - Philippine Data Privacy Act of 2012',
            'ip_address' => $request->ip(),
            'scope' => 'income_verification,utility_history,credit_decisioning,open_banking',
            'agreed_at' => now(),
        ]);

        AuditLog::log('consent_recorded_web', $user->id, [
            'consent_id' => $consent->id,
            'legal_basis' => $consent->legal_basis,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Consent successfully authorized! Proceeding to Open Banking linkage...',
                'redirect' => route('smile.link'),
            ]);
        }

        return redirect()->route('smile.link')
            ->with('success', 'Consent authorized under RA 10173. Connect your accounts to analyze credit limits.');
    }

    /**
     * Show Open Banking Provider Linking View
     */
    public function showSmileLinking()
    {
        $user = Auth::user();

        $linkedAccounts = $user->smileAccounts()
            ->pluck('smile_user_id')
            ->toArray();

        return view(
            'smile.linking',
            compact('user', 'linkedAccounts')
        );
    }

    /**
     * Handle Open Banking Provider Link Simulation
     */
    public function connectSmileProvider(Request $request)
    {
        $user = Auth::user();

        $providerName = $request->input(
            'provider',
            'BDO Unibank'
        );

        $category = $request->input(
            'category',
            'Bank'
        );

        $account = SmileAccount::create([
            'user_id' => $user->id,
            'smile_user_id' =>
                strtolower(str_replace(' ', '_', $providerName))
                . '_' . $user->id,
            'status' => 'connected',
            'connected_at' => now(),
        ]);

        // Add simulated transaction
        Transaction::create([
            'user_id' => $user->id,
            'source' => $providerName,
            'type' => 'credit',
            'amount' => 12500.00,
            'description' => "Verified payout from {$providerName}",
            'transaction_date' => now()->subDays(2),
        ]);

        // Recompute credit score
        $score = CreditScore::where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($score) {
            $newScore = min(
                96,
                $score->score + 4
            );

            $newCeiling = min(
                50000.00,
                $score->approved_ceiling + 5000.00
            );

            $factors = $score->shap_factors ?? [];

            array_unshift($factors, [
                'displayText' =>
                    "Connected verified account: {$providerName}",
                'impactPoints' => 6,
                'direction' => 'positive',
            ]);

            $score->update([
                'score' => $newScore,
                'risk_tier' => (
                    $newScore >= 80
                        ? 'Prime'
                        : ($newScore >= 60 ? 'Standard' : 'Subprime')
                ),
                'approved_ceiling' => $newCeiling,
                'shap_factors' => array_slice($factors, 0, 5),
                'scored_at' => now(),
            ]);
        }

        AuditLog::log('smile_account_linked', $user->id, [
            'provider' => $providerName,
            'account_id' => $account->id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' =>
                    "Successfully connected {$providerName}! Your alternative credit score has been upgraded.",
                'redirect' => route('dashboard'),
            ]);
        }

        return redirect()->route('dashboard')
            ->with(
                'success',
                "{$providerName} connected! Your alternative credit score has been updated."
            );
    }

    /**
     * Show Privacy Policy
     */
    public function showPrivacy()
    {
        return view('compliance.privacy');
    }

    /**
     * Update Profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'id_type'      => 'nullable|string|max:100',
            'id_number'    => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        AuditLog::log(
            'profile_updated_web',
            $user->id,
            $validated
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Profile updated successfully!',
                'user'    => array_merge($user->toArray(), [
                    'profile_picture_url' => $user->profile_picture
                        ? asset('storage/' . $user->profile_picture)
                        : null,
                ]),
            ]);
        }

        return back()->with(
            'success',
            'Profile updated successfully!'
        );
    }

    /**
     * Upload / Replace Profile Picture
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Delete old file if it exists
        if ($user->profile_picture) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $user->update(['profile_picture' => $path]);

        AuditLog::log('profile_picture_updated', $user->id, ['path' => $path]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile picture updated successfully!',
            'user'    => array_merge($user->fresh()->toArray(), [
                'profile_picture_url' => asset('storage/' . $path),
            ]),
        ]);
    }


    /**
     * Manager / Underwriter Dashboard
     */
    public function managerDashboard(Request $request)
    {
        $manager = Auth::user();

        if (
            $manager->role !== 'manager' &&
            $manager->user_type !== 'manager'
        ) {
            return redirect()->route('dashboard')
                ->with(
                    'error',
                    'Manager permissions required.'
                );
        }

        $this->ensureReferenceApplicantsExist();

        $statusFilter = $request->query(
            'status',
            'all'
        );

        $search = $request->query('q', '');

        $query = LoanApplication::with([
            'user.latestCreditScore'
        ])->latest('id');

        if ($statusFilter !== 'all') {
            $query->where(
                'status',
                $statusFilter
            );
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where(
                    'purpose',
                    'like',
                    "%{$search}%"
                )
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where(
                        'name',
                        'like',
                        "%{$search}%"
                    )
                    ->orWhere(
                        'email',
                        'like',
                        "%{$search}%"
                    );
                });
            });
        }

        $loans = $query->get();

        $counts = [
            'total' => LoanApplication::count(),
            'pending' => LoanApplication::where(
                'status',
                'pending'
            )->count(),
            'approved' => LoanApplication::where(
                'status',
                'approved'
            )->count(),
            'rejected' => LoanApplication::where(
                'status',
                'rejected'
            )->count(),
        ];

        return view(
            'manager.dashboard',
            compact(
                'manager',
                'loans',
                'counts',
                'statusFilter',
                'search'
            )
        );
    }

    /**
     * Manager Decision on Loan
     */
    public function managerDecision(
        Request $request,
        $id
    ) {
        $manager = Auth::user();

        if (
            $manager->role !== 'manager' &&
            $manager->user_type !== 'manager'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $validated = $request->validate([
            'decision' =>
                'required|in:approved,rejected,manual_check',
            'notes' =>
                'nullable|string|max:500',
        ]);

        $loan = LoanApplication::with('user')
            ->findOrFail($id);

        $oldStatus = $loan->status;

        $loan->status =
            $validated['decision'];

        $loan->notes =
            $validated['notes']
            ?? $loan->notes;

        if (
            $validated['decision'] === 'approved'
        ) {
            $loan->approved_at = now();
            $loan->disbursed_at = now();
            $loan->status = 'approved';

            $existingDisb = Transaction::where(
                'user_id',
                $loan->user_id
            )
                ->where(
                    'description',
                    'like',
                    "%Loan #{$loan->id}%"
                )
                ->first();

            if (!$existingDisb) {
                Transaction::create([
                    'user_id' =>
                        $loan->user_id,
                    'source' =>
                        'Finclude Capital Disbursal',
                    'type' => 'credit',
                    'amount' =>
                        $loan->amount,
                    'description' =>
                        "Micro-Loan Approved (Loan #{$loan->id} - {$loan->purpose})",
                    'transaction_date' =>
                        now(),
                ]);
            }
        }

        $loan->save();

        AuditLog::log(
            'manager_loan_decision',
            $manager->id,
            [
                'loan_id' => $loan->id,
                'applicant' =>
                    $loan->user
                    ? $loan->user->name
                    : 'N/A',
                'old_status' =>
                    $oldStatus,
                'decision' =>
                    $loan->status,
                'notes' =>
                    $validated['notes'] ?? null,
            ]
        );

        if (
            $request->wantsJson() ||
            $request->ajax()
        ) {
            return response()->json([
                'status' => 'success',
                'message' =>
                    "Application for " .
                    ($loan->user->name ?? 'applicant') .
                    " marked as " .
                    strtoupper($loan->status) .
                    ".",
                'loan' => $loan,
            ]);
        }

        return back()->with(
            'success',
            "Application #{$id} updated to " .
            strtoupper($loan->status)
        );
    }

    /**
     * Manager Remove Loan from Queue
     */
    public function managerRemoveLoan(
        Request $request,
        $id
    ) {
        $manager = Auth::user();

        if (
            $manager->role !== 'manager' &&
            $manager->user_type !== 'manager'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 403);
        }

        $loan = LoanApplication::findOrFail($id);

        $name = $loan->user
            ? $loan->user->name
            : "Loan #{$id}";

        $loan->delete();

        AuditLog::log(
            'manager_loan_removed',
            $manager->id,
            [
                'loan_id' => $id,
                'applicant' => $name,
            ]
        );

        if (
            $request->wantsJson() ||
            $request->ajax()
        ) {
            return response()->json([
                'status' => 'success',
                'message' =>
                    "Application for {$name} removed from queue.",
            ]);
        }

        return back()->with(
            'success',
            "Application for {$name} deleted."
        );
    }

    /**
     * Manager Profile Sheet View
     */
    public function managerProfile()
    {
        $manager = Auth::user();

        $recentAuditLogs = AuditLog::where(
            'user_id',
            $manager->id
        )
            ->latest('id')
            ->take(8)
            ->get();

        return view(
            'manager.profile',
            compact(
                'manager',
                'recentAuditLogs'
            )
        );
    }

    /**
     * Seeds initial demo accounts
     */
    private function ensureDemoAccountsExist()
    {
        // Manager Demo
        User::firstOrCreate(
            ['email' => 'manager@finclude.ph'],
            [
                'name' =>
                    'Credit Manager',
                'username' => 'manager',
                'password' =>
                    Hash::make('manager123'),
                'phone_number' =>
                    '+63 918 555 1234',
                'id_type' =>
                    'PRC Professional ID',
                'id_number' =>
                    'PRC-098271',
                'role' => 'manager',
                'user_type' => 'manager',
            ]
        );

        // Gig Worker Demo
        $worker = User::firstOrCreate(
            ['email' => 'juan.delacruz@finclude.ph'],
            [
                'name' =>
                    'Juan Dela Cruz',
                'username' =>
                    'juan_dela_cruz',
                'password' =>
                    Hash::make('password123'),
                'phone_number' =>
                    '+63 917 890 1234',
                'id_type' =>
                    'Philippine National ID (PhilSys)',
                'id_number' =>
                    '1234-5678-9012-3456',
                'role' =>
                    'gig_worker',
                'user_type' =>
                    'gig_worker',
            ]
        );

        // Ensure Worker has consent
        Consent::firstOrCreate(
            ['user_id' => $worker->id],
            [
                'has_agreed' => true,
                'policy_version' => '1.0',
                'legal_basis' =>
                    'RA 10173 - Philippine Data Privacy Act of 2012',
                'ip_address' =>
                    '127.0.0.1',
                'scope' =>
                    'income_verification,utility_history,credit_decisioning',
                'agreed_at' => now(),
            ]
        );

        // Ensure Worker has credit score
        CreditScore::firstOrCreate(
            ['user_id' => $worker->id],
            [
                'score' => 82,
                'risk_tier' => 'Prime',
                'model_version' => '2.1.0',
                'approved_ceiling' => 25000.00,
                'projected_disposable_cash_flow' =>
                    18500.00,
                'shap_factors' => [
                    [
                        'displayText' =>
                            'Strong verified monthly income (₱38,000)',
                        'impactPoints' => 13,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Consistent on-time utility payments',
                        'impactPoints' => 10,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Verified gig platform earnings (Grab/Maya)',
                        'impactPoints' => 8,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Disciplined day-to-day spending trajectory',
                        'impactPoints' => 5,
                        'direction' => 'positive'
                    ],
                ],
                'scored_at' => now(),
            ]
        );
    }

    /**
     * Seeds reference applicants
     */
    private function ensureReferenceApplicantsExist()
    {
        if (LoanApplication::count() > 0) {
            return;
        }

        $mockApplicants = [
            [
                'name' => 'Maria Santos',
                'email' =>
                    'maria.santos@finclude.ph',
                'purpose' =>
                    'Sari-Sari Store Inventory Expansion',
                'amount' => 15000.00,
                'tenure' => 90,
                'score' => 92,
                'tier' => 'Prime',
                'status' => 'pending',
                'factors' => [
                    [
                        'displayText' =>
                            'Steady GrabFood & Sari-sari daily inflows',
                        'impactPoints' => 15,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Zero default utility & electricity payment history',
                        'impactPoints' => 12,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Healthy debt-to-income margin (12%)',
                        'impactPoints' => 8,
                        'direction' => 'positive'
                    ],
                ]
            ],
            [
                'name' => 'Juan Dela Cruz',
                'email' =>
                    'juan.delacruz@finclude.ph',
                'purpose' =>
                    'Tricycle Engine Overhaul & Maintenance',
                'amount' => 8000.00,
                'tenure' => 61,
                'score' => 78,
                'tier' => 'Standard',
                'status' => 'pending',
                'factors' => [
                    [
                        'displayText' =>
                            'Regular Angkas & JoyRide active transport shifts',
                        'impactPoints' => 10,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Verified Maya & GCash customer settlements',
                        'impactPoints' => 8,
                        'direction' => 'positive'
                    ],
                    [
                        'displayText' =>
                            'Recent irregular weekly vehicle repair outflows',
                        'impactPoints' => 4,
                        'direction' => 'negative'
                    ],
                ]
            ],
            [
                'name' => 'Elena Reyes',
                'email' =>
                    'elena.reyes@finclude.ph',
                'purpose' =>
                    'Agri-Supplies & Vegetable Seed Batch',
                'amount' => 20000.00,
                'tenure' => 120,
                'score' => 45,
                'tier' => 'Subprime',
                'status' => 'pending',
                'factors' => [
                    [
                        'displayText' =>
                            'Significant income variance across billing cycles',
                        'impactPoints' => 18,
                        'direction' => 'negative'
                    ],
                    [
                        'displayText' =>
                            'Late Meralco & water utility notice flagged',
                        'impactPoints' => 12,
                        'direction' => 'negative'
                    ],
                    [
                        'displayText' =>
                            'Active TikTok Shop merchant payout history',
                        'impactPoints' => 5,
                        'direction' => 'positive'
                    ],
                ]
            ],
        ];

        foreach ($mockApplicants as $mock) {
            $user = User::firstOrCreate(
                ['email' => $mock['email']],
                [
                    'name' => $mock['name'],
                    'username' =>
                        strtolower(
                            str_replace(
                                ' ',
                                '_',
                                $mock['name']
                            )
                        ),
                    'password' =>
                        Hash::make('password123'),
                    'phone_number' =>
                        '+63 9' .
                        rand(
                            100000000,
                            999999999
                        ),
                    'id_type' =>
                        'Philippine National ID (PhilSys)',
                    'id_number' =>
                        rand(1000, 9999) .
                        '-' .
                        rand(1000, 9999) .
                        '-' .
                        rand(1000, 9999),
                    'role' =>
                        'gig_worker',
                    'user_type' =>
                        'gig_worker',
                ]
            );

            CreditScore::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'score' =>
                        $mock['score'],
                    'risk_tier' =>
                        $mock['tier'],
                    'model_version' =>
                        '2.1.0',
                    'approved_ceiling' =>
                        $mock['amount'] * 1.5,
                    'projected_disposable_cash_flow' =>
                        16500.00,
                    'shap_factors' =>
                        $mock['factors'],
                    'scored_at' =>
                        now(),
                ]
            );

            $apr = 24.0;

            $interest =
                $mock['amount'] *
                ($apr / 100.0) *
                ($mock['tenure'] / 365.0);

            $total =
                $mock['amount'] +
                $interest;

            $monthly =
                $total /
                ($mock['tenure'] / 30.0);

            LoanApplication::create([
                'user_id' =>
                    $user->id,
                'amount' =>
                    $mock['amount'],
                'tenure_days' =>
                    $mock['tenure'],
                'interest_rate' =>
                    $apr,
                'total_repayment' =>
                    $total,
                'monthly_amortization' =>
                    $monthly,
                'purpose' =>
                    $mock['purpose'],
                'status' =>
                    $mock['status'],
                'due_date' =>
                    now()->addDays(
                        $mock['tenure']
                    ),
                'applied_at' =>
                    now(),
            ]);
        }
    }
}