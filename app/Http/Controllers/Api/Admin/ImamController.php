<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Imam;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImamRequest;
use App\Http\Requests\UpdateImamRequest;
use App\Http\Resources\ImamResource;
use App\Http\Resources\ImamDetailResource;
use Illuminate\Support\Str;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;

class ImamController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:imams.index'], only: ['index']),
            new Middleware(['permission:imams.create'], only: ['store']),
            new Middleware(['permission:imams.edit'], only: ['update', 'updateStatus']),
            new Middleware(['permission:imams.delete'], only: ['destroy']),
        ];
    }

    public function index()
    {
        $query = Imam::with(['profileMasjid', 'createdBy', 'updatedBy']);

        $imams = $query->latest()->paginate(4);

        return new ImamResource(true, 'List Data Imam', ImamDetailResource::collection($imams));
    }

    public function store(StoreImamRequest $request)
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

        $imam = Imam::create([
            'profile_masjid_id' => $profileMasjidId,
            'slug' => Str::slug($validated['nama'] . '-' . time()),
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_active' => $validated['is_active'] ?? true,
            ...array_diff_key($validated, array_flip(['is_active']))
        ]);

        return new ImamResource(true, 'Data imam berhasil disimpan.', new ImamDetailResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function show(Imam $imam)
    {
        return new ImamResource(true, 'Detail data imam berhasil dimuat.', new ImamDetailResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function update(UpdateImamRequest $request, Imam $imam)
    {
        $validated = $request->validated();
        $user = $request->user();

        $imam->update([
            'updated_by' => $user->id,
            ...$validated
        ]);

        return new ImamResource(true, 'Data imam berhasil diupdate.', new ImamDetailResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])));
    }

    public function destroy(Imam $imam)
    {
        // Cek apakah imam sedang digunakan di jadwal khutbah
        $usedInJadwal = \App\Models\JadwalKhutbah::where('imam_id', $imam->id)->exists();

        if ($usedInJadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Imam tidak dapat dihapus karena sedang digunakan dalam jadwal khutbah.'
            ], 422);
        }

        $imam->delete();

        return new ImamResource(true, 'Data imam berhasil dihapus.', null);
    }

    /**
     * Memperbarui status aktif imam.
     */
    public function updateStatus(Request $request, Imam $imam)
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

        $imam->update([
            'is_active' => $request->is_active,
            'updated_by' => $user->id
        ]);

        return new ImamResource(true, 'Status imam berhasil diperbarui!', new ImamDetailResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])));
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
