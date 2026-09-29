<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Consent;
use App\Models\SmileAccount;
use App\Models\CreditScore;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'juan.delacruz@finclude.ph'],
            [
                'name' => 'Juan Dela Cruz',
                'username' => 'juan_dela_cruz',
                'password' => Hash::make('password123'),
                'phone_number' => '+63 917 890 1234',
                'id_type' => 'Philippine National ID (PhilSys)',
                'id_number' => '1234-5678-9012-3456',
                'role' => 'gig_worker',
                'user_type' => 'gig_worker',
            ]
        );

        // Seed Consent
        Consent::create([
            'user_id' => $user->id,
            'has_agreed' => true,
            'policy_version' => '1.0',
            'legal_basis' => 'RA 10173 - Data Privacy Act of 2012',
            'ip_address' => '127.0.0.1',
            'scope' => 'income_verification,utility_history,credit_decisioning',
            'agreed_at' => now(),
        ]);

        // Seed Smile Account
        SmileAccount::create([
            'user_id' => $user->id,
            'smile_user_id' => 'smile_usr_' . $user->id,
            'status' => 'connected',
            'connected_at' => now(),
        ]);

        // Seed Initial Credit Score
        CreditScore::create([
            'user_id' => $user->id,
            'score' => 78,
            'risk_tier' => 'Standard',
            'projected_disposable_cash_flow' => 16500.00,
            'approved_ceiling' => 15000.00,
            'shap_factors' => [
                ['display_text' => '+14 pts: Consistent on-time utility payments', 'impact_points' => 14, 'direction' => 'positive'],
                ['display_text' => '+11 pts: Steady monthly payroll cash flow', 'impact_points' => 11, 'direction' => 'positive'],
                ['display_text' => '+7 pts: Low debt-to-income ratio (16%)', 'impact_points' => 7, 'direction' => 'positive'],
                ['display_text' => '-4 pts: Discretionary retail outflows', 'impact_points' => -4, 'direction' => 'negative'],
            ],
            'psite_metrics' => [
                'cash_flow_velocity' => 7.2,
                'burn_rate_trajectory' => 0.85,
                'utility_recency_days' => 5.0,
                'debt_to_income_ratio' => 0.16,
            ],
            'scored_at' => now(),
        ]);
    }
}
