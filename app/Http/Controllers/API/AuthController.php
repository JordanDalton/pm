<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Create a new API token
     */
    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Delete existing tokens for this device name
        $user->tokens()->where('name', $request->device_name)->delete();
        
        // Create a new token
        $token = $user->createToken($request->device_name);

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
    
    /**
     * Generate a token for the authenticated user
     * (Used when a user is already authenticated via web session)
     */
    public function generateSessionToken(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated',
            ], 401);
        }
        
        // Device name will be web-session plus timestamp to ensure uniqueness
        $deviceName = 'web-session-' . now()->timestamp;
        
        // Delete old web session tokens (optional cleanup)
        $user->tokens()->where('name', 'like', 'web-session-%')->delete();
        
        // Create a new token
        $token = $user->createToken($deviceName);
        
        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ]);
    }
    
    /**
     * Revoke the user's API tokens
     */
    public function revokeTokens(Request $request)
    {
        $user = $request->user();
        
        // Delete all tokens
        $user->tokens()->delete();
        
        return response()->json([
            'message' => 'All tokens revoked successfully',
        ]);
    }
    
    /**
     * Get the authenticated user
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}