<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DataValidationService
{
    /**
     * Noise keywords indicating internal cash-in/cash-out or self-transfers that should be excluded.
     */
    protected array $noiseKeywords = [
        'cash-in', 'cash in', 'fund transfer to own', 'self transfer',
        'load wallet', 'wallet top-up', 'top up', 'transfer to gcash',
        'transfer to maya', 'internal transfer', 'deposit reversal'
    ];

    /**
     * Validate, clean, and standardize a batch of incoming transactions.
     *
     * @param array $rawTransactions
     * @param string|null $sourceName
     * @return array [
     *    'valid_transactions' => array,
     *    'rejected_transactions' => array,
     *    'quality_gate' => [
     *        'total_records' => int,
     *        'valid_records' => int,
     *        'rejected_records' => int,
     *        'completeness_rate' => float, // 0.0 - 100.0%
     *        'validity_rate' => float,     // 0.0 - 100.0%
     *        'passed_quality_gate' => bool
     *    ]
     * ]
     */
    public function processBatch(array $rawTransactions, ?string $sourceName = null): array
    {
        $total = count($rawTransactions);
        if ($total === 0) {
            return [
                'valid_transactions' => [],
                'rejected_transactions' => [],
                'quality_gate' => [
                    'total_records' => 0,
                    'valid_records' => 0,
                    'rejected_records' => 0,
                    'completeness_rate' => 0.0,
                    'validity_rate' => 0.0,
                    'passed_quality_gate' => false,
                ]
            ];
        }

        $valid = [];
        $rejected = [];
        $seenTxIds = [];
        $completeFieldsCount = 0;
        $totalRequiredFieldsCount = $total * 4; // amount, description, type/category, date

        foreach ($rawTransactions as $index => $tx) {
            $errors = [];

            // 1. Completeness Check: Check presence of core fields
            $hasAmount = isset($tx['amount']) && is_numeric($tx['amount']);
            $hasDesc = !empty($tx['description']);
            $hasTypeOrCat = !empty($tx['type']) || !empty($tx['category']);
            $dateVal = $tx['date'] ?? $tx['transaction_date'] ?? $tx['created_at'] ?? null;
            $hasDate = !empty($dateVal);

            if ($hasAmount) $completeFieldsCount++;
            if ($hasDesc) $completeFieldsCount++;
            if ($hasTypeOrCat) $completeFieldsCount++;
            if ($hasDate) $completeFieldsCount++;

            if (!$hasAmount || !$hasDesc) {
                $errors[] = 'Incomplete record: amount and description are mandatory.';
            }

            // 2. Format & Range Check: Amount must be numeric and realistic (> 0 and < 10,000,000 PHP)
            $amount = floatval($tx['amount'] ?? 0);
            if ($amount <= 0.0) {
                $errors[] = 'Invalid amount: must be greater than zero.';
            } elseif ($amount > 10000000.0) {
                $errors[] = 'Extreme outlier amount exceeding PHP 10,000,000 threshold.';
            }

            // 3. Timestamp Check: Date format parseable and within reasonable window (not future > 1 day, not older than 5 years)
            $parsedDate = null;
            if ($hasDate) {
                try {
                    $parsedDate = Carbon::parse($dateVal);
                    if ($parsedDate->isAfter(now()->addDay())) {
                        $errors[] = 'Future timestamp detected.';
                    } elseif ($parsedDate->isBefore(now()->subYears(5))) {
                        $errors[] = 'Timestamp older than 5 years window.';
                    }
                } catch (\Exception $e) {
                    $errors[] = 'Unparseable date timestamp format: ' . $dateVal;
                }
            } else {
                $parsedDate = now();
            }

            // 4. Duplicate Check: Deduplicate transaction_id within the batch
            $txId = $tx['id'] ?? $tx['transaction_id'] ?? null;
            if ($txId) {
                if (in_array($txId, $seenTxIds)) {
                    $errors[] = 'Duplicate transaction_id within payload: ' . $txId;
                } else {
                    $seenTxIds[] = $txId;
                }
            }

            // 5. Logical Consistency Check: Type vs description consistency
            $type = strtolower($tx['type'] ?? '');
            $desc = strtolower($tx['description'] ?? '');

            if (empty($type)) {
                if (str_contains($desc, 'payroll') || str_contains($desc, 'salary') || str_contains($desc, 'deposit') || str_contains($desc, 'remittance') || str_contains($desc, 'earnings')) {
                    $type = 'credit';
                } else {
                    $type = 'debit';
                }
            } elseif (!in_array($type, ['credit', 'debit', 'inflow', 'outflow'])) {
                $errors[] = 'Invalid transaction type: ' . $type;
            } else {
                if ($type === 'inflow') $type = 'credit';
                if ($type === 'outflow') $type = 'debit';
            }

            // 6. Source Check: Verify source if provided
            if ($sourceName && !in_array(strtolower($sourceName), ['gcash', 'maya', 'grab', 'foodpanda', 'shopee', 'lazada', 'bdo', 'bpi', 'unionbank', 'smile', 'manual'])) {
                $errors[] = 'Unverified data source: ' . $sourceName;
            }

            // 7. Cleaning: Check if noise transaction (e.g. self cash-in loop)
            $isNoise = false;
            foreach ($this->noiseKeywords as $noiseKeyword) {
                if (str_contains($desc, $noiseKeyword)) {
                    $isNoise = true;
                    break;
                }
            }

            if (!empty($errors)) {
                $rejected[] = [
                    'index' => $index,
                    'raw_record' => $tx,
                    'errors' => $errors
                ];
            } else {
                $valid[] = [
                    'transaction_id' => $txId ?? ('tx_' . uniqid()),
                    'amount' => round($amount, 2),
                    'type' => $type,
                    'category' => $tx['category'] ?? $this->inferCategory($desc, $type),
                    'description' => trim($tx['description']),
                    'date' => $parsedDate ? $parsedDate->toIso8601String() : now()->toIso8601String(),
                    'is_noise' => $isNoise,
                    'raw_payload' => $tx,
                ];
            }
        }

        // Quality Gate Metrics
        $validCount = count($valid);
        $rejectedCount = count($rejected);
        $completenessRate = round(($completeFieldsCount / $totalRequiredFieldsCount) * 100, 2);
        $validityRate = round(($validCount / $total) * 100, 2);
        $passedGate = ($validityRate >= 70.0 && $completenessRate >= 75.0);

        Log::info("Data Processing Quality Gate: Completeness={$completenessRate}%, Validity={$validityRate}%, Passed=" . ($passedGate ? 'YES' : 'NO'));

        return [
            'valid_transactions' => $valid,
            'rejected_transactions' => $rejected,
            'quality_gate' => [
                'total_records' => $total,
                'valid_records' => $validCount,
                'rejected_records' => $rejectedCount,
                'completeness_rate' => $completenessRate,
                'validity_rate' => $validityRate,
                'passed_quality_gate' => $passedGate,
            ]
        ];
    }

    /**
     * Infer transaction category from description and type.
     */
    protected function inferCategory(string $desc, string $type): string
    {
        $d = strtolower($desc);
        if (str_contains($d, 'salary') || str_contains($d, 'payroll') || str_contains($d, 'bpo') || str_contains($d, 'remittance') || str_contains($d, 'grab income') || str_contains($d, 'earnings')) {
            return 'salary';
        }
        if (str_contains($d, 'meralco') || str_contains($d, 'maynilad') || str_contains($d, 'manila water') || str_contains($d, 'electric') || str_contains($d, 'utility')) {
            return 'utility';
        }
        if (str_contains($d, 'pldt') || str_contains($d, 'globe') || str_contains($d, 'smart') || str_contains($d, 'converge') || str_contains($d, 'telecom')) {
            return 'telecom';
        }
        if (str_contains($d, 'home credit') || str_contains($d, 'loan') || str_contains($d, 'amortization') || str_contains($d, 'credit card') || str_contains($d, 'tala') || str_contains($d, 'billease')) {
            return 'loan';
        }
        if (str_contains($d, 'puregold') || str_contains($d, 'sm supermarket') || str_contains($d, 'robinsons') || str_contains($d, 'grocery') || str_contains($d, 'market')) {
            return 'groceries';
        }
        return $type === 'credit' ? 'income' : 'general_expense';
    }
}
