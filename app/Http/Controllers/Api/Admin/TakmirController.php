<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Takmir;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\TakmirResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
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
            new Middleware(['permission:takmirs.edit'], only: ['update']),
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

            // 3. Upload gambar
            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('photos', $imageName, 'public');

            // 4. Buat data Takmir dengan audit columns
            return Takmir::create([
                'user_id' => $newUser->id,
                'profile_masjid_id' => $profileMasjid->id,
                'image' => $imageName,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
                ...$validated // Gunakan sisa data yang sudah divalidasi
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

        $imageName = $takmir->image;

        if ($request->hasFile('image')) {
            if ($takmir->image) Storage::disk('public')->delete('photos/' . $takmir->image);

            $imageName = time() . '.' . $request->file('image')->getClientOriginalExtension();
            $request->file('image')->storeAs('photos', $imageName, 'public');
        }

        DB::transaction(function () use ($takmir, $validated, $imageName, $user) {
            // Update data takmir dengan audit column
            $takmir->update(array_merge($validated, [
                'image' => $imageName,
                'updated_by' => $user->id
            ]));

            // Sinkronkan nama di tabel user jika berubah
            if ($takmir->user && $takmir->user->name !== $validated['nama']) {
                $takmir->user->update(['name' => $validated['nama']]);
            }
        });

        return new TakmirResource(true, 'Data takmir berhasil diperbarui!', $takmir->load(['user', 'profileMasjid']));
    }

    public function destroy(Takmir $takmir)
    {
        DB::transaction(function () use ($takmir) {
            if ($takmir->image) {
                Storage::disk('public')->delete('photos/' . $takmir->image);
            }

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
