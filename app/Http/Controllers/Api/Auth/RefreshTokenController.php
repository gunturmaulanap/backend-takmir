<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class RefreshTokenController extends Controller
{
    /**
     * Generate refresh token string
     */
    private function generateRefreshToken(): string
    {
        return hash('sha256', Str::random(60) . time());
    }

    /**
     * Create new refresh token for user
     */
    private function createRefreshToken(User $user, Request $request): RefreshToken
    {
        return RefreshToken::create([
            'user_id' => $user->id,
            'token' => $this->generateRefreshToken(),
            'expires_at' => Carbon::now()->addDays(7), // 7 days expiry
            'device_info' => $request->header('User-Agent'),
            'ip_address' => $request->ip(),
        ]);
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token is required'
            ], 422);
        }

        try {
            $refreshToken = RefreshToken::where('token', $request->refresh_token)
                ->where('revoked', false)
                ->with('user')
                ->first();

            if (!$refreshToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid refresh token'
                ], 401);
            }

            if ($refreshToken->isExpired()) {
                // Revoke expired token
                $refreshToken->revoke();

                return response()->json([
                    'success' => false,
                    'message' => 'Refresh token expired. Please login again.'
                ], 401);
            }

            // Check if user is still active
            $user = $refreshToken->user;
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is inactive. Please contact admin.'
                ], 403);
            }

            // Generate new access token
            $newAccessToken = JWTAuth::fromUser($user);

            // Delete old refresh token and create new one
            $refreshToken->delete();
            $newRefreshToken = $this->createRefreshToken($user, $request);

            return response()->json([
                'success' => true,
                'access_token' => $newAccessToken,
                'refresh_token' => $newRefreshToken->token,
                'expires_in' => config('jwt.ttl') * 60, // Convert minutes to seconds
                'token_type' => 'Bearer'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Revoke refresh token (logout) - Delete token permanently
     */
    public function revoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Refresh token is required'
            ], 422);
        }

        try {
            $refreshToken = RefreshToken::where('token', $request->refresh_token)
                ->where('revoked', false)
                ->first();

            if ($refreshToken) {
                // Delete permanently instead of just revoke
                $refreshToken->delete();
            }

            return response()->json([
                'success' => true,
                'message' => 'Refresh token deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}