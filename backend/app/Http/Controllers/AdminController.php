<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoanApplication;
use App\Models\CreditScore;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin Dashboard
    |--------------------------------------------------------------------------
    */

    public function dashboard(Request $request)
    {
        $this->ensureAdmin($request);

        $totalUsers = User::count();

        $totalWorkers = User::where(function ($q) {
            $q->where('role', 'gig_worker')
                ->orWhere('user_type', 'gig_worker');
        })->count();

        $totalManagers = User::where(function ($q) {
            $q->where('role', 'manager')
                ->orWhere('user_type', 'manager');
        })->count();

        $totalAdmins = User::where(function ($q) {
            $q->where('role', 'admin')
                ->orWhere('user_type', 'admin');
        })->count();

        $totalLoans = LoanApplication::count();

        $pendingLoans = LoanApplication::where(
            'status',
            'pending'
        )->count();

        $approvedLoans = LoanApplication::whereIn(
            'status',
            ['approved', 'disbursed']
        )->count();

        $rejectedLoans = LoanApplication::where(
            'status',
            'rejected'
        )->count();

        $totalLoanAmount = (float) LoanApplication::sum('amount');

        $creditScores = CreditScore::select('score')->get();

        $primeScores = $creditScores
            ->where('score', '>=', 80)
            ->count();

        $standardScores = $creditScores
            ->whereBetween('score', [60, 79])
            ->count();

        $highRiskScores = $creditScores
            ->where('score', '<', 60)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Recent Users
        |--------------------------------------------------------------------------
        */

        $recentUsers = User::latest('id')
            ->take(10)
            ->get([
                'id',
                'name',
                'email',
                'username',
                'phone_number',
                'role',
                'user_type',
                'created_at',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Recent Loans
        |--------------------------------------------------------------------------
        */

        $recentLoans = LoanApplication::with([
            'user.latestCreditScore',
        ])
            ->latest('id')
            ->take(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent Audit Logs
        |--------------------------------------------------------------------------
        */

        $recentAuditLogs = AuditLog::latest('id')
            ->take(15)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Dashboard Summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_users' => $totalUsers,
            'workers' => $totalWorkers,
            'managers' => $totalManagers,
            'admins' => $totalAdmins,

            'total_loans' => $totalLoans,
            'pending_loans' => $pendingLoans,
            'approved_loans' => $approvedLoans,
            'rejected_loans' => $rejectedLoans,
            'total_loan_amount' => $totalLoanAmount,

            'prime_scores' => $primeScores,
            'standard_scores' => $standardScores,
            'high_risk_scores' => $highRiskScores,
        ];

        return view('admin.dashboard', [
            'summary' => $summary,
            'recentUsers' => $recentUsers,
            'recentLoans' => $recentLoans,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | List Users
    |--------------------------------------------------------------------------
    */

    public function users(Request $request)
    {
        $this->ensureAdmin($request);

        $query = User::query()->latest('id');

        /*
        |--------------------------------------------------------------------------
        | Filter by role
        |--------------------------------------------------------------------------
        */

        if ($request->filled('role')) {
            $role = $request->query('role');

            $query->where(function ($q) use ($role) {
                $q->where('role', $role)
                    ->orWhere('user_type', $role);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Search users
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim($request->query('search'));

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'status' => 'success',
            'users' => $query->paginate(20),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | User Profile
    |--------------------------------------------------------------------------
    */

    public function userProfile(Request $request, $userId)
    {
        $this->ensureAdmin($request);

        $user = User::with([
            'latestCreditScore',
            'dataSources',
            'loanApplications' => function ($q) {
                $q->latest('id');
            },
            'transactions' => function ($q) {
                $q->latest('transaction_date')
                    ->take(20);
            },
        ])->findOrFail($userId);

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Get User for Edit
    |--------------------------------------------------------------------------
    */

    public function editUser(Request $request, $userId)
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($userId);

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Update User
    |--------------------------------------------------------------------------
    */

    public function updateUser(Request $request, $userId)
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($userId);

        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id,
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username,' . $user->id,
            ],

            'phone_number' => 'nullable|string|max:30',

            'role' => [
                'required',
                'string',
                'in:admin,manager,gig_worker',
            ],

            'password' => 'nullable|string|min:8',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Prevent Admin From Changing Their Own Role
        |--------------------------------------------------------------------------
        */

        if (
            $user->id === $request->user()->id &&
            $validated['role'] !== 'admin'
        ) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot remove your own admin role.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Basic Information
        |--------------------------------------------------------------------------
        */

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->username = $validated['username'];
        $user->phone_number = $validated['phone_number'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Keep Role and User Type Synchronized
        |--------------------------------------------------------------------------
        */

        $user->role = $validated['role'];
        $user->user_type = $validated['role'];

        /*
        |--------------------------------------------------------------------------
        | Change Password Only When Supplied
        |--------------------------------------------------------------------------
        */

        if (!empty($validated['password'])) {
            $user->password = Hash::make(
                $validated['password']
            );
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        $updatedFields = [
            'name',
            'email',
            'username',
            'phone_number',
            'role',
            'user_type',
        ];

        if (!empty($validated['password'])) {
            $updatedFields[] = 'password';
        }

        AuditLog::log(
            'admin_updated_user',
            $request->user()->id,
            [
                'user_id' => $user->id,
                'updated_fields' => $updatedFields,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Deactivate User
    |--------------------------------------------------------------------------
    */

    public function deactivateUser(Request $request, $userId)
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($userId);

        /*
        |--------------------------------------------------------------------------
        | Prevent Admin From Deactivating Their Own Account
        |--------------------------------------------------------------------------
        */

        if ($user->id === $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'You cannot deactivate your own account.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Determine Active Status Column
        |--------------------------------------------------------------------------
        */

        $statusColumn = $this->getActiveStatusColumn();

        if (!$statusColumn) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active status column was found in the users table. Expected: is_active, status, or active.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Set User as Inactive
        |--------------------------------------------------------------------------
        */

        if ($statusColumn === 'is_active') {
            $user->is_active = false;
        } elseif ($statusColumn === 'active') {
            $user->active = false;
        } elseif ($statusColumn === 'status') {
            $user->status = 'inactive';
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        AuditLog::log(
            'admin_deactivated_user',
            $request->user()->id,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'status_column' => $statusColumn,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User deactivated successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Restore User
    |--------------------------------------------------------------------------
    */

    public function restoreUser(Request $request, $userId)
    {
        $this->ensureAdmin($request);

        $user = User::findOrFail($userId);

        /*
        |--------------------------------------------------------------------------
        | Determine Active Status Column
        |--------------------------------------------------------------------------
        */

        $statusColumn = $this->getActiveStatusColumn();

        if (!$statusColumn) {
            return response()->json([
                'status' => 'error',
                'message' => 'No active status column was found in the users table. Expected: is_active, status, or active.',
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | Set User as Active
        |--------------------------------------------------------------------------
        */

        if ($statusColumn === 'is_active') {
            $user->is_active = true;
        } elseif ($statusColumn === 'active') {
            $user->active = true;
        } elseif ($statusColumn === 'status') {
            $user->status = 'active';
        }

        $user->save();

        /*
        |--------------------------------------------------------------------------
        | Audit Log
        |--------------------------------------------------------------------------
        */

        AuditLog::log(
            'admin_restored_user',
            $request->user()->id,
            [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'status_column' => $statusColumn,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'User restored successfully.',
            'user' => $user,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Find Active Status Column
    |--------------------------------------------------------------------------
    */

    private function getActiveStatusColumn(): ?string
    {
        if (Schema::hasColumn('users', 'is_active')) {
            return 'is_active';
        }

        if (Schema::hasColumn('users', 'active')) {
            return 'active';
        }

        if (Schema::hasColumn('users', 'status')) {
            return 'status';
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Loans
    |--------------------------------------------------------------------------
    */

    public function loans(Request $request)
    {
        $this->ensureAdmin($request);

        $query = LoanApplication::with([
            'user',
            'user.latestCreditScore',
        ])->latest('id');

        if (
            $request->filled('status') &&
            $request->query('status') !== 'all'
        ) {
            $query->where(
                'status',
                $request->query('status')
            );
        }

        return response()->json([
            'status' => 'success',
            'loans' => $query->paginate(20),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Audit Logs
    |--------------------------------------------------------------------------
    */

    public function auditLogs(Request $request)
    {
        $this->ensureAdmin($request);

        $logs = AuditLog::latest('id')
            ->paginate(30);

        return response()->json([
            'status' => 'success',
            'logs' => $logs,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Create Admin
    |--------------------------------------------------------------------------
    */

    public function createAdmin(Request $request)
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'username' => [
                'required',
                'string',
                'max:100',
                'unique:users,username',
            ],

            'password' => 'required|string|min:8',

            'phone_number' => 'nullable|string|max:30',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $validated['username'],

            'password' => Hash::make(
                $validated['password']
            ),

            'phone_number' => $validated['phone_number'] ?? null,

            'role' => 'admin',
            'user_type' => 'admin',
        ]);

        AuditLog::log(
            'admin_created',
            $request->user()->id,
            [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'username' => $admin->username,
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Admin account created successfully.',
            'admin' => $admin,
        ], 201);
    }


    /*
    |--------------------------------------------------------------------------
    | Admin Authorization
    |--------------------------------------------------------------------------
    */

    private function ensureAdmin(Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user &&
            (
                $user->role === 'admin' ||
                $user->user_type === 'admin'
            ),
            403,
            'Admin access required.'
        );
    }
}