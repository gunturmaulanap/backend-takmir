<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Asatidz;
use App\Models\Jamaah;
use App\Http\Resources\AsatidzResource;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use App\Http\Requests\StoreAsatidzRequest;
use App\Http\Requests\UpdateAsatidzRequest;

class AsatidzController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:asatidzs.index'], only: ['index']),
            new Middleware(['permission:asatidzs.create'], only: ['store']),
            new Middleware(['permission:asatidzs.edit'], only: ['update']),
            new Middleware(['permission:asatidzs.delete'], only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar asatidz dengan murid TPQ.
     */
    public function index()
    {
        $query = Asatidz::with(['profileMasjid', 'createdBy', 'updatedBy', 'murid' => function ($query) {
            $query->where('aktivitas_jamaah', 'like', '%TPQ%');
        }]);

        $asatidz = $query->latest()->paginate(10);
        return AsatidzResource::collection($asatidz);
    }

    /**
     * Menyimpan data asatidz baru.
     */
    public function store(StoreAsatidzRequest $request)
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

        $asatidz = Asatidz::create([
            'profile_masjid_id' => $profileMasjidId,
            'slug' => Str::slug($validated['nama']) . '-' . Str::random(5),
            'created_by' => $user->id,
            'updated_by' => $user->id,
            ...$validated
        ]);

        // Attach murid TPQ jika ada
        if (isset($validated['murid_ids']) && is_array($validated['murid_ids'])) {
            $muridTPQ = Jamaah::whereIn('id', $validated['murid_ids'])
                ->where('profile_masjid_id', $profileMasjidId)
                ->where('aktivitas_jamaah', 'like', '%TPQ%')
                ->get()
                ->pluck('id')
                ->toArray();

            $asatidz->murid()->attach($muridTPQ);
        }

        return new AsatidzResource(true, 'Data asatidz berhasil disimpan.', $asatidz->load(['profileMasjid', 'createdBy', 'updatedBy', 'murid']));
    }

    /**
     * Menampilkan detail satu asatidz.
     */
    public function show(Asatidz $asatidz)
    {
        $asatidz->load(['profileMasjid', 'createdBy', 'updatedBy', 'murid' => function ($query) {
            $query->where('aktivitas_jamaah', 'like', '%TPQ%');
        }]);

        return new AsatidzResource(true, 'Detail data asatidz', $asatidz);
    }

    /**
     * Mendapatkan daftar murid TPQ yang tersedia untuk dipilih
     */
    public function getAvailableMuridTPQ()
    {
        $user = request()->user();
        $profileMasjidId = $user->getMasjidProfile()->id;

        $muridTPQ = Jamaah::where('profile_masjid_id', $profileMasjidId)
            ->where('aktivitas_jamaah', 'like', '%TPQ%')
            ->get(['id', 'nama', 'no_handphone', 'umur', 'jenis_kelamin']);

        return response()->json([
            'success' => true,
            'data' => $muridTPQ
        ]);
    }

    /**
     * Memperbarui data asatidz.
     */
    public function update(UpdateAsatidzRequest $request, Asatidz $asatidz)
    {
        $validated = $request->validated();
        $user = $request->user();

        $asatidz->update([
            'slug' => Str::slug($validated['nama']) . '-' . Str::random(5),
            'updated_by' => $user->id,
            ...$validated
        ]);

        // Sync murid TPQ
        if (isset($validated['murid_ids']) && is_array($validated['murid_ids'])) {
            $muridTPQ = Jamaah::whereIn('id', $validated['murid_ids'])
                ->where('profile_masjid_id', $asatidz->profile_masjid_id)
                ->where('aktivitas_jamaah', 'like', '%TPQ%')
                ->get()
                ->pluck('id')
                ->toArray();

            $asatidz->murid()->sync($muridTPQ);
        } else {
            $asatidz->murid()->detach();
        }

        return new AsatidzResource(true, 'Data asatidz berhasil diupdate.', $asatidz->load(['profileMasjid', 'createdBy', 'updatedBy', 'murid']));
    }

    /**
     * Menghapus data asatidz.
     */
    public function destroy($id)
    {
        try {
            $user = request()->user();
            $profileMasjidId = $user->getMasjidProfile()->id;

            $asatidz = Asatidz::where('id', $id)
                ->where('profile_masjid_id', $profileMasjidId)
                ->first();

            if (!$asatidz) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data asatidz tidak ditemukan atau tidak memiliki akses.'
                ], 404);
            }

            // Detach semua murid
            $asatidz->murid()->detach();

            $asatidz->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data asatidz berhasil dihapus.',
                'data' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data asatidz.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get profile masjid ID berdasarkan role user
     */
    private function getProfileMasjidId($user, $request)
    {
        if ($user->roles->contains('name', 'superadmin')) {
            return $request->get('profile_masjid_id');
        }

        $profileMasjid = $user->getMasjidProfile();
        return $profileMasjid ? $profileMasjid->id : null;
    }
}
