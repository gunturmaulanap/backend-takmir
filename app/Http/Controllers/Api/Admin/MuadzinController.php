<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Muadzin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuadzinRequest;
use App\Http\Requests\UpdateMuadzinRequest;
use App\Http\Resources\MuadzinResource;
use App\Http\Resources\MuadzinDetailResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;

class MuadzinController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:muadzins.index'], only: ['index']),
            new Middleware(['permission:muadzins.create'], only: ['store']),
            new Middleware(['permission:muadzins.edit'], only: ['update', 'updateStatus']),
            new Middleware(['permission:muadzins.delete'], only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = Muadzin::with(['profileMasjid', 'createdBy', 'updatedBy']);

        $muadzins = $query->latest()->paginate(4);

        return new MuadzinResource(true, 'List Data Muadzin', MuadzinDetailResource::collection($muadzins));
    }

    public function store(StoreMuadzinRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $muadzin = Muadzin::create([
            'profile_masjid_id' => $profileMasjidId,
            'slug' => Str::slug($validated['nama'] . '-' . time()),
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_active' => $validated['is_active'] ?? true,
            ...array_diff_key($validated, array_flip(['is_active']))
        ]);

        return new MuadzinResource(true, 'Data muadzin berhasil disimpan.', new MuadzinDetailResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function show(Muadzin $muadzin)
    {
        return new MuadzinResource(true, 'Detail data muadzin berhasil dimuat.', new MuadzinDetailResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function update(UpdateMuadzinRequest $request, Muadzin $muadzin)
    {
        $validated = $request->validated();
        $user = $request->user();

        $muadzin->update([
            'updated_by' => $user->id,
            ...$validated
        ]);

        return new MuadzinResource(true, 'Data muadzin berhasil diupdate.', new MuadzinDetailResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function destroy(Muadzin $muadzin)
    {
        // Cek apakah muadzin sedang digunakan di jadwal khutbah
        $usedInJadwal = \App\Models\JadwalKhutbah::where('muadzin_id', $muadzin->id)->exists();

        if ($usedInJadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Muadzin tidak dapat dihapus karena sedang digunakan dalam jadwal khutbah.'
            ], 422);
        }

        $muadzin->delete();

        return new MuadzinResource(true, 'Data muadzin berhasil dihapus.', null);
    }

    /**
     * Memperbarui status aktif muadzin.
     */
    public function updateStatus(Request $request, Muadzin $muadzin)
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

        $muadzin->update([
            'is_active' => $request->is_active,
            'updated_by' => $user->id
        ]);

        return new MuadzinResource(true, 'Status muadzin berhasil diperbarui!', new MuadzinDetailResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    /**
     * Get profile masjid ID berdasarkan role user
     */
    private function getProfileMasjidId($user, $request)
    {
        if ($user->roles->contains('name', 'superadmin')) {
            return $request->get('profile_masjid_id');
        }

        // Untuk admin dan takmir, gunakan method getMasjidProfile untuk konsistensi
        $profileMasjid = $user->getMasjidProfile();
        return $profileMasjid ? $profileMasjid->id : null;
    }
}
