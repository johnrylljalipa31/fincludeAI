<?php

namespace App\Http\Controllers;

use App\Models\Consent;
use Illuminate\Http\Request;

class ConsentController extends Controller
{
    public function agree(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'policy_version' => 'nullable|string',
            'legal_basis' => 'nullable|string',
            'scope' => 'nullable|string',
        ]);

        $consent = Consent::create([
            'user_id' => $user->id,
            'has_agreed' => true,
            'policy_version' => $validated['policy_version'] ?? '1.0',
            'legal_basis' => $validated['legal_basis'] ?? 'RA 10173 - Philippine Data Privacy Act of 2012',
            'ip_address' => $request->ip(),
            'scope' => $validated['scope'] ?? 'income_verification,utility_history,credit_decisioning',
            'agreed_at' => now(),
        ]);

        \App\Models\AuditLog::log('consent_recorded_ra10173', $user->id, [
            'consent_id' => $consent->id,
            'policy_version' => $consent->policy_version,
            'legal_basis' => $consent->legal_basis,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Consent recorded in compliance with RA 10173',
            'consent' => $consent,
        ], 200);
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $consent = $user->latestConsent;

        return response()->json([
            'status' => 'success',
            'has_agreed' => $consent ? $consent->has_agreed : false,
            'consent' => $consent,
        ]);
    }
}
