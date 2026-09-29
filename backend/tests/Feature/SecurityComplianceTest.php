<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SecurityComplianceTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_payloads_containing_passwords_pins_or_otps_data_minimization()
    {
        $user = User::factory()->create();

        // Attempting to send a PIN or OTP in webhook
        $payloadWithOtp = [
            'event' => 'TRANSACTIONS_ADDED',
            'userId' => $user->id,
            'pin' => '1234',
            'otp' => '987654',
            'data' => [
                'transactions' => [
                    ['description' => 'Test Transaction', 'amount' => 1000]
                ]
            ]
        ];

        $response = $this->postJson('/api/webhook/smile', $payloadWithOtp);

        $response->assertStatus(422);
        $response->assertJsonPath('status', 'rejected');
        $this->assertStringContainsString('Data minimization violation', $response->json('error'));

        // Check that an audit log of the violation was recorded
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'security_violation_sensitive_data_attempt',
            'status' => 'blocked',
        ]);
    }

    public function test_passwords_are_securely_hashed()
    {
        $plainPassword = 'MySecretPassword#2026';
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@finclude.ph',
            'password' => $plainPassword,
        ]);

        $response->assertStatus(201);
        $user = User::where('email', 'juan@finclude.ph')->first();

        $this->assertNotNull($user);
        $this->assertNotEquals($plainPassword, $user->password);
        $this->assertTrue(Hash::check($plainPassword, $user->password));
    }

    public function test_protected_endpoints_require_valid_sanctum_token()
    {
        // Without Authorization header
        $this->getJson('/api/dashboard')->assertStatus(401);
        $this->postJson('/api/consent/agree', [])->assertStatus(401);
        $this->postJson('/api/loan/apply', [])->assertStatus(401);
    }
}
