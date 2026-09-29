<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Consent;
use App\Models\CreditScore;
use App\Models\DataSource;
use App\Models\Explanation;
use App\Models\Feature;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndPipelineIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_pipeline_from_registration_to_xai_dashboard()
    {
        // 1. User Registration
        $registerResponse = $this->postJson('/api/auth/register', [
            'name' => 'Maria Santos',
            'email' => 'maria.santos@finclude.ph',
            'password' => 'SecurePass123!',
            'phone_number' => '+639171234567',
            'id_type' => 'Philippine National ID',
            'id_number' => '1234-5678-9012-3456',
        ]);

        $registerResponse->assertStatus(201);
        $token = $registerResponse->json('token');
        $userId = $registerResponse->json('user.id');
        $this->assertNotEmpty($token);

        // 2. RA 10173 Consent
        $consentResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/consent/agree', [
                'policy_version' => '1.0',
                'legal_basis' => 'RA 10173 - Philippine Data Privacy Act of 2012',
                'scope' => 'income_verification,utility_history,credit_decisioning',
            ]);

        $consentResponse->assertStatus(200);
        $this->assertDatabaseHas('consents', [
            'user_id' => $userId,
            'has_agreed' => true,
            'legal_basis' => 'RA 10173 - Philippine Data Privacy Act of 2012',
        ]);

        // 3. Webhook Ingestion with Data Validation & Quality Gate
        $webhookPayload = [
            'event' => 'TRANSACTIONS_ADDED',
            'userId' => $userId,
            'provider' => 'GCash',
            'data' => [
                'transactions' => [
                    [
                        'id' => 'tx_gcash_01',
                        'description' => 'Foodpanda Rider Delivery Earnings',
                        'amount' => 14000.00,
                        'type' => 'credit',
                        'category' => 'income',
                        'date' => now()->subDays(10)->toIso8601String(),
                    ],
                    [
                        'id' => 'tx_gcash_02',
                        'description' => 'Meralco Electric Bill Payment',
                        'amount' => 2400.00,
                        'type' => 'debit',
                        'category' => 'utility',
                        'date' => now()->subDays(6)->toIso8601String(),
                    ],
                    [
                        'id' => 'tx_gcash_03',
                        'description' => 'Home Credit Monthly Amortization',
                        'amount' => 2100.00,
                        'type' => 'debit',
                        'category' => 'loan',
                        'date' => now()->subDays(4)->toIso8601String(),
                    ]
                ]
            ]
        ];

        $webhookResponse = $this->postJson('/api/webhook/smile', $webhookPayload);
        $webhookResponse->assertStatus(200);
        $webhookResponse->assertJsonPath('quality_gate.passed_quality_gate', true);

        // 4. Verify 8 Core Tables Persistence
        $this->assertDatabaseHas('users', ['id' => $userId]);
        $this->assertDatabaseHas('consents', ['user_id' => $userId]);
        $this->assertDatabaseHas('data_sources', ['user_id' => $userId, 'provider' => 'GCash']);
        $this->assertDatabaseHas('transactions', ['user_id' => $userId, 'description' => 'Foodpanda Rider Delivery Earnings']);
        $this->assertDatabaseHas('features', ['user_id' => $userId]);
        $this->assertDatabaseHas('credit_scores', ['user_id' => $userId]);
        $this->assertDatabaseHas('explanations', ['user_id' => $userId]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $userId]);

        // 5. Dashboard Retrieval
        $dashboardResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard');

        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertJsonStructure([
            'status',
            'user' => ['id', 'name', 'email', 'role'],
            'credit_score' => ['score', 'risk_tier', 'approved_ceiling', 'shap_factors'],
            'compliance_disclosures' => ['min_tenure_days', 'max_tenure_days', 'annual_percentage_rate_apr']
        ]);

        $score = $dashboardResponse->json('credit_score.score');
        $this->assertGreaterThanOrEqual(10, $score);
        $this->assertLessThanOrEqual(100, $score);
    }
}
