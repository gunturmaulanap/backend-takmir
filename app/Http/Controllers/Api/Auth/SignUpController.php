<?php

namespace App\Http\Controllers\Api\Auth;

use App\Models\User;
use App\Models\ProfileMasjid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Storage; // Tambahkan ini

class SignUpController extends Controller
{
    /**
     * Handle user registration and create a corresponding ProfileMasjid.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // Set validation rules for user and masjid profile data, termasuk validasi image
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|max:255',
            'username'      => 'required|string|max:255|unique:users,username',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'nama_masjid'   => 'required|string|max:255',
            'alamat_masjid' => 'required|string|max:255',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Tambahkan validasi image
        ]);

        // Return validation errors if they occur
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Gagal!',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Use database transaction to ensure both user and profile are created or none
        DB::beginTransaction();

        try {
            // Check if the 'admin' role exists
            $adminRole = Role::where('name', 'admin')->first();
            if (!$adminRole) {
                throw new \Exception('Role "admin" tidak ditemukan. Silakan buat role ini terlebih dahulu.');
            }

            // Upload image jika ada
            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('photos', $imageName, 'public');
            }

            // Create the user with the provided data
            $user = User::create([
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            // Assign the 'admin' role to the new user
            $user->assignRole($adminRole);

            // Create the ProfileMasjid entry linked to the new user dengan slug
            ProfileMasjid::create([
                'user_id' => $user->id,
                'nama'    => $request->nama_masjid,
                'alamat'  => $request->alamat_masjid,
                'slug'    => \Illuminate\Support\Str::slug($request->nama_masjid),
                'image'   => $imageName,
            ]);

            DB::commit();

            // Generate a JWT token for the newly created user
            $token = JWTAuth::fromUser($user);

            // Return a success response with user data and token
            return response()->json([
                'success'       => true,
                'message'       => 'Registrasi admin dan profil masjid berhasil!',
                'user'          => $user->only(['name', 'username', 'email']),
                'profile'       => ProfileMasjid::where('user_id', $user->id)->first(),
                'permissions'   => $user->getPermissionArray(),
                'token'         => $token
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan registrasi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
