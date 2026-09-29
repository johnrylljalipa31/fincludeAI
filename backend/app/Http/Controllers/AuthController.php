<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|max:50|unique:users',
            'password' => 'required|string|min:6',
            'phone_number' => 'nullable|string|max:20',
            'id_type' => 'nullable|string|max:50',
            'id_number' => 'nullable|string|max:50',
        ]);

        $username = $validated['username'] ?? explode('@', $validated['email'])[0];
        // Ensure username uniqueness if fallback is used
        if (User::where('username', $username)->exists()) {
            $username .= '_' . rand(100, 999);
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_number' => $validated['phone_number'] ?? null,
            'id_type' => $validated['id_type'] ?? 'Philippine National ID',
            'id_number' => $validated['id_number'] ?? null,
            'role' => 'gig_worker',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        \App\Models\AuditLog::log('user_registered', $user->id, [
            'email' => $user->email,
            'username' => $user->username,
            'role' => $user->role,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'id_type' => $user->id_type,
                'id_number' => $user->id_number,
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $identifierInput = $request->input('login') ?? $request->input('email') ?? $request->input('username');
        $request->merge(['identifier' => $identifierInput]);

        $validated = $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $identifier = $validated['identifier'];
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Revoke old tokens optionally, create fresh token
        $token = $user->createToken('auth_token')->plainTextToken;

        \App\Models\AuditLog::log('user_login', $user->id, [
            'email' => $user->email,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'role' => $user->role ?? 'gig_worker',
                'has_consented' => $user->latestConsent && $user->latestConsent->has_agreed,
            ]
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['latestConsent', 'latestCreditScore', 'smileAccounts']);

        return response()->json([
            'status' => 'success',
            'user' => $user,
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:20',
        ]);

        $user->update($validated);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile updated successfully',
            'user'    => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'email'               => $user->email,
                'phone_number'        => $user->phone_number,
                'role'                => $user->role ?? 'gig_worker',
                'has_consented'       => $user->latestConsent && $user->latestConsent->has_agreed,
                'profile_picture_url' => $user->profile_picture
                    ? asset('storage/' . $user->profile_picture)
                    : null,
            ],
        ]);
    }

    /**
     * Upload / Replace Profile Picture (Mobile API)
     */
    public function uploadProfilePicture(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        // Delete old file if it exists
        if ($user->profile_picture) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_picture);
        }

        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        $user->update(['profile_picture' => $path]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Profile picture updated successfully!',
            'user'    => [
                'id'                  => $user->id,
                'name'                => $user->name,
                'email'               => $user->email,
                'phone_number'        => $user->phone_number,
                'role'                => $user->role ?? 'gig_worker',
                'has_consented'       => $user->latestConsent && $user->latestConsent->has_agreed,
                'profile_picture_url' => asset('storage/' . $path),
            ],
        ]);
    }


    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
        ]);

        $identifier = $validated['email'];
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with the provided email or username.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Account identified. You can now reset your password.',
            'user' => [
                'email' => $user->email,
                'name' => $user->name,
            ]
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        $identifier = $validated['email'];
        $user = User::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'No account found with the provided email or username.',
            ], 404);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        \App\Models\AuditLog::log('password_reset_success', $user->id, [
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Password reset successfully! You can now sign in with your new password.',
        ]);
    }
}
