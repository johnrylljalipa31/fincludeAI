<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AiEngineService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = env('AI_ENGINE_URL', 'http://127.0.0.1:8000');
    }

    public function assessCredit(string $userId, int $accountAgeDays, array $transactions): array
    {
        $response = Http::post("{$this->baseUrl}/api/v1/assess-credit", [
            'user_id' => $userId,
            'account_age_days' => $accountAgeDays,
            'transactions' => $transactions,
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [
            'status' => 'error',
            'message' => 'Failed to process credit scoring with AI engine.',
        ];
    }
}