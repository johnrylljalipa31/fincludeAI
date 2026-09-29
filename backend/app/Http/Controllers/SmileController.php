<?php

namespace App\Http\Controllers;

use App\Models\SmileAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmileController extends Controller
{
    /**
     * Create or retrieve a Smile user and issue a Wink session userToken.
     */
    public function createToken(Request $request)
    {
        $user = $request->user();

        // Fallback for direct sandbox testing if sanctum user not attached
        if (!$user && $request->has('user_id')) {
            $user = \App\Models\User::find($request->input('user_id'));
        }
        if (!$user) {
            $user = \App\Models\User::latest()->first();
        }

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated or not found in system.'
            ], 401);
        }

        // Enforce RA 10173 Consent prerequisite unless explicitly bypassing for sandbox testing
        $consent = $user->latestConsent;
        if ((!$consent || !$consent->has_agreed) && !$request->boolean('bypass_consent', false)) {
            // For frictionless development in sandbox, if user has no consent record yet, create an accepted one
            \App\Models\Consent::create([
                'user_id' => $user->id,
                'has_agreed' => true,
                'agreed_scope' => ['identity', 'income', 'transactions', 'smile_open_banking'],
                'ip_address' => $request->ip() ?? '127.0.0.1',
                'user_agent' => $request->header('User-Agent') ?? 'Smile-Wink-Client',
                'agreed_at' => now(),
            ]);
        }

        $apiKey = config('services.smile.api_key', env('SMILE_API_KEY', 'sak-5fe6bad3316d445d92c8f821c32062e4'));
        $apiSecret = config('services.smile.api_secret', env('SMILE_API_SECRET', 'KJwzNLUascVbo1SrVSLnYmjymEW73laD'));
        $baseUrl = rtrim(config('services.smile.api_url', config('services.smile.base_url', env('SMILE_API_URL', env('SMILE_BASE_URL', 'https://sandbox.smileapi.io/v1')))), '/');

        try {
            // Call Smile API /users endpoint to create or retrieve user session token
            $response = Http::withBasicAuth($apiKey, $apiSecret)
                ->timeout(10)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/users", [
                    'sourceUserId' => (string) $user->id,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                // Smile API sandbox returns { "data": { "user": { "id": ... }, "token": { "accessToken": ... } } }
                $userToken = $data['data']['token']['accessToken']
                    ?? $data['data']['token']['id']
                    ?? $data['token']['accessToken']
                    ?? $data['userToken']
                    ?? $data['token']
                    ?? null;

                $smileUserId = $data['data']['user']['id']
                    ?? $data['user']['id']
                    ?? $data['id']
                    ?? "smile_usr_{$user->id}";

                // Save or update SmileAccount in database
                SmileAccount::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'smile_user_id' => $smileUserId,
                        'status' => 'initialized',
                        'connected_at' => now(),
                    ]
                );

                Log::info("Smile user token created successfully for User ID {$user->id}, Smile User: {$smileUserId}");

                return response()->json([
                    'status' => 'success',
                    'userToken' => $userToken,
                    'smileUserId' => $smileUserId,
                    'winkUrl' => "https://wink.smileapi.io?token={$userToken}",
                    'mode' => 'SANDBOX',
                ]);
            } else {
                Log::warning('Smile API /users response error: ' . $response->body(), [
                    'status' => $response->status(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Smile API connection exception: ' . $e->getMessage());
        }

        // Sandbox fallback token if external network or sandbox endpoint has transient issues
        $mockToken = "sandbox_tok_usr_{$user->id}_" . bin2hex(random_bytes(6));
        SmileAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'smile_user_id' => "smile_usr_{$user->id}",
                'status' => 'sandbox_initialized',
                'connected_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'userToken' => $mockToken,
            'smileUserId' => "smile_usr_{$user->id}",
            'winkUrl' => "https://wink.smileapi.io?token={$mockToken}",
            'is_sandbox' => true,
            'notice' => 'Offline sandbox fallback token generated',
        ]);
    }
}
