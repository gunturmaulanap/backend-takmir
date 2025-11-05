<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * Handle user login and return a JWT token.
     */
    public function index(Request $request)
    {
        // Validasi hanya butuh login (username/email) dan password
        $validator = Validator::make($request->all(), [
            'id'       => 'required|string',
            'password' => 'required',
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

            // Cek jika user role takmir, validasi status aktif
            if ($user->roles->contains('name', 'takmir') && $user->takmir) {
                if (!$user->takmir->is_active) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun takmir tidak aktif. Silakan hubungi admin.'
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
            $permissions = $user->getAllPermissions()->pluck('name')->toArray();

            return response()->json([
                'success'        => true,
                'user'           => $userData,
                'profile_masjid' => $masjidProfile,
                'permissions'    => $permissions,
                'token'          => $token
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
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json([
            'success' => true,
        ], 200);
    }
}
