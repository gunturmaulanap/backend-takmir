<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Takmir;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\TakmirResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Requests\StoreTakmirRequest;
use App\Http\Requests\UpdateTakmirRequest;

class TakmirController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:takmirs.index'], only: ['index']),
            new Middleware(['permission:takmirs.create'], only: ['store']),
            new Middleware(['permission:takmirs.edit'], only: ['update', 'updateStatus']),
            new Middleware(['permission:takmirs.delete'], only: ['destroy']),
        ];
    }

    public function index()
    {
        $takmirs = Takmir::with(['user', 'profileMasjid'])->latest()->paginate(10);
        return new TakmirResource(true, 'List Data Takmirs', $takmirs);
    }

    public function store(StoreTakmirRequest $request)
    {
        $validated = $request->validated();
        $adminUser = $request->user();

        if (!$adminUser) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.'
            ], 403);
        }

        // Gunakan method getMasjidProfile untuk konsistensi
        $profileMasjid = $adminUser->getMasjidProfile();

        if (!$profileMasjid) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $takmir = DB::transaction(function () use ($request, $validated, $profileMasjid, $adminUser) {
            // 1. Buat User baru untuk takmir (tanpa email, pakai username saja)
            $newUser = User::create([
                'name'     => $validated['nama'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Berikan role 'takmir'
            $newUser->assignRole('takmir');

            // 3. Buat data Takmir dengan audit columns
            return Takmir::create([
                'user_id' => $newUser->id,
                'profile_masjid_id' => $profileMasjid->id,
                'nama' => $validated['nama'],
                'slug' => Str::slug($validated['nama']),
                'jabatan' => $validated['jabatan'],
                'no_handphone' => $validated['no_handphone'] ?? null,
                'umur' => $validated['umur'] ?? null,
                'deskripsi_tugas' => $validated['deskripsi_tugas'] ?? null,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);
        });

        return new TakmirResource(true, 'Data takmir berhasil disimpan.', $takmir->load(['user', 'profileMasjid']));
    }

    public function show(Takmir $takmir)
    {
        return new TakmirResource(true, 'Detail data takmir berhasil dimuat.', $takmir->load(['user', 'profileMasjid']));
    }

    public function update(UpdateTakmirRequest $request, Takmir $takmir)
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.'
            ], 403);
        }

        DB::transaction(function () use ($takmir, $validated, $user) {
            // Prepare data untuk update takmir (exclude user fields)
            $takmirUpdateData = [
                'nama' => $validated['nama'],
                'jabatan' => $validated['jabatan'],
                'no_handphone' => $validated['no_handphone'] ?? null,
                'umur' => $validated['umur'] ?? null,
                'deskripsi_tugas' => $validated['deskripsi_tugas'] ?? null,
                'updated_by' => $user->id
            ];

            // Update data takmir
            $takmir->update($takmirUpdateData);

            // Update data user terkait jika ada
            if ($takmir->user) {
                $userData = [];

                // Update nama user (sync dengan takmir.nama)
                if ($takmir->user->name !== $validated['nama']) {
                    $userData['name'] = $validated['nama'];
                }

                // Update username jika dikirim
                if (isset($validated['username']) && $validated['username'] !== $takmir->user->username) {
                    $userData['username'] = $validated['username'];
                }

                // Update email jika dikirim
                if (isset($validated['email']) && $validated['email'] !== $takmir->user->email) {
                    $userData['email'] = $validated['email'];
                }

                // Update password jika dikirim
                if (isset($validated['password']) && !empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                // Update user jika ada perubahan
                if (!empty($userData)) {
                    $takmir->user->update($userData);
                }
            }
        });

        return new TakmirResource(true, 'Data takmir berhasil diperbarui!', $takmir->load(['user', 'profileMasjid']));
    }

    public function updateStatus(Request $request, Takmir $takmir)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.'
            ], 403);
        }

        if (!$takmir->user) {
            return response()->json([
                'success' => false,
                'message' => 'User terkait tidak ditemukan.'
            ], 404);
        }

        // Update status di tabel users (single source of truth)
        $takmir->user->update([
            'is_active' => $request->is_active
        ]);

        // Update audit trail di tabel takmir
        $takmir->update([
            'updated_by' => $user->id
        ]);

        $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return new TakmirResource(true, "Takmir berhasil {$status}!", $takmir->load(['user', 'profileMasjid']));
    }

    public function destroy(Takmir $takmir)
    {
        DB::transaction(function () use ($takmir) {
            // Hapus user terkait terlebih dahulu
            if ($takmir->user) {
                $takmir->user->delete();
            }

            // Hapus data takmir
            $takmir->delete();
        });

        return new TakmirResource(true, 'Data takmir berhasil dihapus.', null);
    }
}
