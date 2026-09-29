<?php

namespace Tests\Unit;

use App\Services\DataValidationService;
use Tests\TestCase;

class DataValidationServiceTest extends TestCase
{
    protected DataValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DataValidationService();
    }

    public function test_processes_valid_transactions_and_passes_quality_gate()
    {
        $transactions = [
            [
                'id' => 'tx_001',
                'description' => 'Grab Driver Weekly Payout',
                'amount' => 8500.00,
                'type' => 'credit',
                'category' => 'salary',
                'date' => '2026-09-01T08:00:00Z',
            ],
            [
                'id' => 'tx_002',
                'description' => 'Meralco Manila Electric Company',
                'amount' => 2450.50,
                'type' => 'debit',
                'category' => 'utilities',
                'date' => '2026-09-05T08:00:00Z',
            ],
            [
                'id' => 'tx_003',
                'description' => 'Maynilad Water Bill',
                'amount' => 620.00,
                'type' => 'debit',
                'category' => 'utilities',
                'date' => '2026-09-07T08:00:00Z',
            ],
        ];

        $result = $this->service->processBatch($transactions, 'Grab');

        $this->assertCount(3, $result['valid_transactions']);
        $this->assertCount(0, $result['rejected_transactions']);
        $this->assertTrue($result['quality_gate']['passed_quality_gate']);
        $this->assertEquals(100.0, $result['quality_gate']['validity_rate']);
        $this->assertEquals(100.0, $result['quality_gate']['completeness_rate']);
    }

    public function test_flags_and_excludes_invalid_records()
    {
        $transactions = [
            [
                'id' => 'tx_good',
                'description' => 'Foodpanda Rider Payout',
                'amount' => 6200.00,
                'type' => 'credit',
                'date' => '2026-09-02T10:00:00Z',
            ],
            [
                'id' => 'tx_negative',
                'description' => 'Invalid negative amount',
                'amount' => -500.00,
                'date' => '2026-09-02T10:00:00Z',
            ],
            [
                'id' => 'tx_missing_desc',
                'amount' => 1200.00,
                'type' => 'credit',
            ],
            [
                'id' => 'tx_future',
                'description' => 'Future date transaction',
                'amount' => 1000.00,
                'date' => '2035-01-01T00:00:00Z',
            ]
        ];

        $result = $this->service->processBatch($transactions);

        $this->assertCount(1, $result['valid_transactions']);
        $this->assertCount(3, $result['rejected_transactions']);
        $this->assertEquals(25.0, $result['quality_gate']['validity_rate']);
        $this->assertFalse($result['quality_gate']['passed_quality_gate']);
    }

    public function test_detects_noise_transactions()
    {
        $transactions = [
            [
                'id' => 'tx_noise',
                'description' => 'Cash-in via 7-Eleven Cliqq',
                'amount' => 3000.00,
                'date' => '2026-09-03T10:00:00Z',
            ],
            [
                'id' => 'tx_self',
                'description' => 'Fund transfer to own Maya account',
                'amount' => 1500.00,
                'date' => '2026-09-03T11:00:00Z',
            ],
        ];

        $result = $this->service->processBatch($transactions);

        $this->assertCount(2, $result['valid_transactions']);
        $this->assertTrue($result['valid_transactions'][0]['is_noise']);
        $this->assertTrue($result['valid_transactions'][1]['is_noise']);
    }
}
