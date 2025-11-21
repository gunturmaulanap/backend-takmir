<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Models\RefreshToken;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class LoginController extends Controller
{
    /**
     * Handle user login and return a JWT token.
     */
    public function index(Request $request)
    {
        // Validasi dengan sanitization untuk XSS prevention
        $validator = Validator::make($request->all(), [
            'id'       => ['required', 'string', 'max:255', 'regex:/^[a-zA-Z0-9.@._-]+$/'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ], [
            'id.regex' => 'Username/Email format tidak valid. Hanya huruf, angka, @, ., _ dan - yang diperbolehkan.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Deteksi login pakai email atau username
        $loginField = filter_var($request->id, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [
            $loginField => $request->id,
            'password' => $request->password
        ];

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Username/Email atau Kata Sandi salah'
            ], 400);
        }

        try {
            /** @var User $user */
            $user = auth()->guard('api')->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Cek status aktif user (semua role harus aktif)
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi admin.'
                ], 403);
            }

            // Jika role takmir, pastikan data takmir ada dan lengkap (opsional)
            if ($user->roles->contains('name', 'takmir')) {
                if (!$user->takmir) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data takmir tidak ditemukan. Silakan hubungi admin.'
                    ], 403);
                }
            }

            // Ambil role utama user (tanpa input dari request)
            $role = $user->roles->isNotEmpty() ? $user->roles->first()->name : 'user';

            $userData = [
                'id'       => $user->id,
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $role
            ];

            $masjidProfile = $user->getMasjidProfile();
            $permissions = $user->getPermissionArray();

            // Limit active tokens per user (max 5 devices)
            $maxActiveTokens = 5;
            $activeTokens = RefreshToken::where('user_id', $user->id)
                ->where('revoked', false)
                ->orderBy('created_at', 'asc')
                ->get();

            // Delete oldest tokens if exceeds limit
            if ($activeTokens->count() >= $maxActiveTokens) {
                $tokensToDelete = $activeTokens->count() - $maxActiveTokens + 1;
                RefreshToken::where('user_id', $user->id)
                    ->where('revoked', false)
                    ->orderBy('created_at', 'asc')
                    ->limit($tokensToDelete)
                    ->delete();
            }

            // Generate refresh token
            $refreshToken = RefreshToken::create([
                'user_id' => $user->id,
                'token' => hash('sha256', uniqid() . time() . $user->id),
                'expires_at' => Carbon::now()->addDays(7),
                'device_info' => $request->header('User-Agent'),
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success'        => true,
                'user'           => $userData,
                'profile_masjid' => $masjidProfile,
                'permissions'    => $permissions,
                'access_token'   => $token,
                'refresh_token'  => $refreshToken->token,
                'expires_in'     => config('jwt.ttl') * 60, // Convert minutes to seconds
                'token_type'     => 'Bearer'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data user',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Invalidate the JWT token to log the user out.
     */
    public function logout(Request $request)
    {
        try {
            $user = auth()->guard('api')->user();

            // Delete all refresh tokens for this user
            if ($user) {
                $deletedCount = RefreshToken::where('user_id', $user->id)->delete();
            } else {
            }

            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
                'deleted_tokens' => $deletedCount ?? 0
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
