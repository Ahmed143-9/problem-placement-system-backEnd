<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Log the request
        \Log::info('Login attempt', ['username' => $request->username]);

        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            \Log::warning('Invalid credentials', ['username' => $request->username]);
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Check if user status is pending
        if ($user->status === 'pending') {
            \Log::info('Pending user login attempt', ['user_id' => $user->id]);
            return response()->json([
                'message' => 'Your account is pending approval. Please wait for admin approval.',
                'user' => $user,
                'token' => null
            ], 403);
        }

        // Check if user is inactive
        if ($user->status === 'inactive') {
            \Log::warning('Inactive user login attempt', ['user_id' => $user->id]);
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact administrator.',
                'user' => $user,
                'token' => null
            ], 403);
        }

        // Create token
        $token = $user->createToken('auth_token')->plainTextToken;

        \Log::info('Login successful', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'department' => $user->department,
                'status' => $user->status,
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function me(Request $request)
    {
        return response()->json(['user' => $request->user()]);
    }
}