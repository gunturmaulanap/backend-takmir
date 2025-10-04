<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Jamaah;
use App\Http\Resources\JamaahResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use App\Http\Requests\StoreJamaahRequest;
use App\Http\Requests\UpdateJamaahRequest;

class JamaahController extends Controller implements HasMiddleware
{
    // HAPUS CONSTRUCTOR DAN PROPERTI $user & $masjidProfile

    public static function middleware(): array
    {
        return [
            new Middleware(['permission:jamaahs.index'], only: ['index']),
            new Middleware(['permission:jamaahs.create'], only: ['store']),
            new Middleware(['permission:jamaahs.edit'], only: ['update']),
            new Middleware(['permission:jamaahs.delete'], only: ['destroy']),
        ];
    }

    /**
     * Menampilkan daftar jamaah dengan filter dan pagination.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $query = Jamaah::with(['profileMasjid', 'createdBy', 'updatedBy'])
            ->where('profile_masjid_id', $profileMasjidId);

        // Filter berdasarkan nama
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan jenis kelamin
        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->jenis_kelamin);
        }

        // Filter berdasarkan aktivitas jamaah
        if ($request->filled('aktivitas_jamaah')) {
            $query->where('aktivitas_jamaah', 'like', '%' . $request->aktivitas_jamaah . '%');
        }

        $jamaahs = $query->latest()->paginate(10);

        return response()->json(
            JamaahResource::customResponse(true, 'List data jamaah', JamaahResource::collection($jamaahs))
        );
    }

    /**
     * Menyimpan data jamaah baru.
     */
    public function store(StoreJamaahRequest $request)
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

        $jamaah = Jamaah::create([
            'profile_masjid_id' => $profileMasjidId,
            'slug' => Str::slug($validated['nama']),
            'created_by' => $user->id,
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            JamaahResource::customResponse(true, 'Data jamaah berhasil disimpan.', new JamaahResource($jamaah->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    /**
     * Menampilkan detail satu jamaah.
     */
    public function show(Jamaah $jamaah)
    {
        return response()->json(
            JamaahResource::customResponse(true, 'Detail data jamaah', new JamaahResource($jamaah->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    /**
     * Memperbarui data jamaah.
     */
    public function update(UpdateJamaahRequest $request, Jamaah $jamaah)
    {
        $validated = $request->validated();
        $user = $request->user();

        $jamaah->update([
            'slug' => Str::slug($validated['nama']),
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            JamaahResource::customResponse(true, 'Data jamaah berhasil diupdate.', new JamaahResource($jamaah->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    /**
     * Menghapus data jamaah.
     */
    public function destroy(Jamaah $jamaah)
    {
        $jamaah->delete();

        return response()->json(
            JamaahResource::customResponse(true, 'Data jamaah berhasil dihapus.', null)
        );
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
