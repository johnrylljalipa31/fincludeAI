<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LoanApplication;
use App\Models\CreditScore;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_loan_lifecycle()
    {
        $response = $this->get(route('loan.lifecycle'));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_loan_can_view_loan_lifecycle()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('loan.lifecycle'));

        $response->assertStatus(200);
        $response->assertSee('No Active Loan');
        $response->assertSee('Current Finclude Wallet Balance');
    }

    public function test_user_relationships_work_correctly()
    {
        $user = User::factory()->create();

        $loan = LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 5000.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 1765.00,
            'total_repayment' => 5295.00,
            'status' => 'pending',
            'due_date' => now()->addDays(90),
        ]);

        $txn = Transaction::create([
            'user_id' => $user->id,
            'transaction_id' => 'TXN-TEST-1',
            'amount' => 1000.00,
            'type' => 'credit',
            'category' => 'salary',
            'description' => 'Test credit',
            'transaction_date' => now(),
        ]);

        $this->assertTrue($user->loans->contains($loan));
        $this->assertTrue($user->loanApplications->contains($loan));
        $this->assertTrue($user->transactions->contains($txn));
        $this->assertEquals($user->id, $loan->user->id);
    }

    public function test_user_with_pending_loan_view()
    {
        $user = User::factory()->create();

        LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 10000.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 3530.59,
            'total_repayment' => 10591.78,
            'status' => 'pending',
            'purpose' => 'Equipment upgrade',
            'due_date' => now()->addDays(90),
        ]);

        $response = $this->actingAs($user)->get(route('loan.lifecycle'));

        $response->assertStatus(200);
        $response->assertSee('PENDING');
        $response->assertSee('Equipment upgrade');
        $response->assertSee('Application is Pending Review');
        $response->assertSee('Repayment Schedule Breakdown');
    }

    public function test_user_with_rejected_loan_view()
    {
        $user = User::factory()->create();

        LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 10000.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 3530.59,
            'total_repayment' => 10591.78,
            'status' => 'rejected',
            'purpose' => 'Business expansion',
            'notes' => 'Insufficient disposable cash flow verification',
            'due_date' => now()->addDays(90),
        ]);

        $response = $this->actingAs($user)->get(route('loan.lifecycle'));

        $response->assertStatus(200);
        $response->assertSee('REJECTED');
        $response->assertSee('Insufficient disposable cash flow verification');
    }

    public function test_partial_repayment_and_overpayment_prevention()
    {
        $user = User::factory()->create();

        // Create initial score
        CreditScore::create([
            'user_id' => $user->id,
            'score' => 75,
            'risk_tier' => 'Standard',
            'approved_ceiling' => 20000.00,
            'scored_at' => now(),
        ]);

        $loan = LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 9441.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 3333.33,
            'total_repayment' => 10000.00,
            'status' => 'approved',
            'purpose' => 'Working capital',
            'due_date' => now()->addDays(90),
        ]);

        // Attempt overpayment (pay 12000 when remaining is 10000)
        $overpayResponse = $this->actingAs($user)->postJson(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 12000.00,
            'payment_method' => 'gcash',
        ]);

        $overpayResponse->assertStatus(422);
        $overpayResponse->assertJsonFragment(['status' => 'error']);

        // First valid partial payment of 2000 via GCash
        $pay1Response = $this->actingAs($user)->postJson(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 2000.00,
            'payment_method' => 'gcash',
        ]);

        $pay1Response->assertStatus(200);
        $pay1Response->assertJson([
            'status' => 'success',
            'remaining' => 8000.00,
            'is_settled' => false,
        ]);

        $loan->refresh();
        $this->assertEquals(8000.00, (float) $loan->total_repayment);
        $this->assertEquals('approved', $loan->status);

        // Verify transaction created exactly once
        $this->assertEquals(1, Transaction::where('user_id', $user->id)->count());
        $txn = Transaction::where('user_id', $user->id)->first();
        $this->assertEquals(2000.00, (float) $txn->amount);
        $this->assertEquals('debit', $txn->type);
        $this->assertEquals('loan_repayment', $txn->category);

        // Insufficient Wallet Balance Check:
        // Add a debit of 15000 so wallet balance drops to 0
        $largeDebit = Transaction::create([
            'user_id' => $user->id,
            'amount' => 15000.00,
            'type' => 'debit',
            'description' => 'Large debit',
            'transaction_date' => now(),
        ]);

        // Trying to pay 8000 via wallet must fail with 422 because wallet balance is 0.00
        $walletFailResponse = $this->actingAs($user)->postJson(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 8000.00,
            'payment_method' => 'wallet',
        ]);
        $walletFailResponse->assertStatus(422);
        $walletFailResponse->assertJsonFragment(['status' => 'error']);

        // Remove the large debit so user can proceed
        $largeDebit->delete();

        // Second payment of remaining 8000 via Maya -> Full Settlement!
        $pay2Response = $this->actingAs($user)->postJson(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 8000.00,
            'payment_method' => 'maya',
        ]);

        $pay2Response->assertStatus(200);
        $pay2Response->assertJson([
            'status' => 'success',
            'remaining' => 0.00,
            'is_settled' => true,
        ]);

        $loan->refresh();
        $this->assertEquals(0.00, (float) $loan->total_repayment);
        $this->assertEquals('repaid', $loan->status);
        $this->assertNotNull($loan->repaid_at);

        // Verify credit score gained +5 points
        $score = CreditScore::where('user_id', $user->id)->first();
        $this->assertEquals(80, $score->score);

        // Attempting to pay again on already repaid loan must fail with 422
        $againResponse = $this->actingAs($user)->postJson(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 500.00,
            'payment_method' => 'gcash',
        ]);
        $againResponse->assertStatus(422);
        $againResponse->assertJsonFragment(['status' => 'error']);
    }

    public function test_user_with_disbursed_and_repaid_loan_views()
    {
        $user = User::factory()->create();

        // Disbursed loan
        $loan = LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 7000.00,
            'tenure_days' => 61,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 3640.00,
            'total_repayment' => 7280.00,
            'status' => 'disbursed',
            'purpose' => 'Inventory',
            'due_date' => now()->addDays(61),
        ]);

        $response = $this->actingAs($user)->get(route('loan.lifecycle'));
        $response->assertStatus(200);
        $response->assertSee('DISBURSED');
        $response->assertSee('Make Repayment / Settle Loan');
        $response->assertSee('₱7,280.00');

        // Update to repaid
        $loan->update([
            'status' => 'repaid',
            'total_repayment' => 0.00,
            'repaid_at' => now(),
        ]);

        $responseRepaid = $this->actingAs($user)->get(route('loan.lifecycle'));
        $responseRepaid->assertStatus(200);
        $responseRepaid->assertSee('REPAID');
        $responseRepaid->assertSee('Micro-Loan Fully Repaid &amp; Settled', false);
        $responseRepaid->assertDontSee('Make Repayment / Settle Loan');
    }

    public function test_cannot_repay_pending_or_other_users_loan()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $pendingLoan = LoanApplication::create([
            'user_id' => $user1->id,
            'amount' => 5000.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 1765.00,
            'total_repayment' => 5295.00,
            'status' => 'pending',
            'due_date' => now()->addDays(90),
        ]);

        // Attempt to repay pending loan
        $pendingRepay = $this->actingAs($user1)->postJson(route('loan.repay.submit'), [
            'loan_id' => $pendingLoan->id,
            'amount' => 1000.00,
            'payment_method' => 'gcash',
        ]);
        $pendingRepay->assertStatus(422);

        // User2 attempts to repay User1's loan
        $otherUserRepay = $this->actingAs($user2)->postJson(route('loan.repay.submit'), [
            'loan_id' => $pendingLoan->id,
            'amount' => 1000.00,
            'payment_method' => 'gcash',
        ]);
        $otherUserRepay->assertStatus(404);
    }

    public function test_repayment_via_standard_post_redirects_to_lifecycle()
    {
        $user = User::factory()->create();

        $loan = LoanApplication::create([
            'user_id' => $user->id,
            'amount' => 3000.00,
            'tenure_days' => 90,
            'interest_rate_apr' => 24.00,
            'monthly_amortization' => 1059.00,
            'total_repayment' => 3177.00,
            'status' => 'approved',
            'due_date' => now()->addDays(90),
        ]);

        $response = $this->actingAs($user)->post(route('loan.repay.submit'), [
            'loan_id' => $loan->id,
            'amount' => 500.00,
            'payment_method' => 'bdo',
        ]);

        $response->assertRedirect(route('loan.lifecycle'));
        $response->assertSessionHas('success');
    }
}
