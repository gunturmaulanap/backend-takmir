<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Khatib;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreKhatibRequest;
use App\Http\Requests\UpdateKhatibRequest;
use App\Http\Resources\KhatibResource;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class KhatibController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:khatibs.index'], only: ['index']),
            new Middleware(['permission:khatibs.create'], only: ['store']),
            new Middleware(['permission:khatibs.edit'], only: ['update', 'updateStatus']),
            new Middleware(['permission:khatibs.delete'], only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar khatib (otomatis terfilter oleh Global Scope).
     */
    public function index()
    {
        $khatibs = Khatib::latest()->paginate(10);
        return new KhatibResource(true, 'Daftar Data Khatib', $khatibs);
    }

    /**
     * Menyimpan data khatib baru.
     */
    public function store(StoreKhatibRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.'
            ], 403);
        }

        // Untuk admin, ambil profile_masjid_id dari user, untuk superadmin dari request
        $profileMasjidId = $validated['profile_masjid_id'] ?? ($user->profileMasjid ? $user->profileMasjid->id : null);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $khatib = Khatib::create(array_merge($validated, [
            'profile_masjid_id' => $profileMasjidId,
            'slug' => Str::slug($validated['nama'] . '-' . time()),
            'is_active' => $validated['is_active'] ?? true,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]));

        return new KhatibResource(true, 'Data khatib berhasil disimpan.', $khatib);
    }

    /**
     * Menampilkan detail satu khatib (otomatis aman).
     */
    public function show(Khatib $khatib)
    {
        return new KhatibResource(true, 'Detail Data Khatib', $khatib);
    }

    /**
     * Memperbarui data khatib.
     */
    public function update(UpdateKhatibRequest $request, Khatib $khatib)
    {
        $validated = $request->validated();
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak terautentikasi.'
            ], 403);
        }

        $updateData = $validated;
        // Buat slug baru jika nama diubah
        if (isset($validated['nama'])) {
            $updateData['slug'] = Str::slug($validated['nama'] . '-' . time());
        }
        $updateData['updated_by'] = $user->id;

        $khatib->update($updateData);

        return new KhatibResource(true, 'Khatib berhasil diperbarui.', $khatib);
    }

    /**
     * Memperbarui status aktif khatib.
     */
    public function updateStatus(Request $request, Khatib $khatib)
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

        $khatib->update([
            'is_active' => $request->is_active,
            'updated_by' => $user->id
        ]);

        return new KhatibResource(true, 'Status khatib berhasil diperbarui!', $khatib);
    }

    /**
     * Menghapus data khatib.
     */
    public function destroy(Khatib $khatib)
    {
        $khatib->delete();
        return new KhatibResource(true, 'Khatib berhasil dihapus.', null);
    }
}
