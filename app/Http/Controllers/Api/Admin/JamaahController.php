<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Jamaah;
use App\Http\Resources\JamaahResource;
use App\Http\Controllers\Controller;
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
     * Trait HasMasjid akan otomatis memfilter berdasarkan masjid yang login.
     */
    public function index()
    {
        $query = Jamaah::with(['profileMasjid', 'createdBy', 'updatedBy']);

        $jamaahs = $query->latest()->paginate(4);
        return new JamaahResource(true, 'List Data Jamaah', $jamaahs);
    }

    /**
     * Menyimpan data jamaah baru.
     */
    public function store(StoreJamaahRequest $request)
    {
        $validated = $request->validated();
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        // Untuk superadmin, profile_masjid_id harus disediakan
        if ($user->roles->contains('name', 'superadmin') && !$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Superadmin harus menyertakan profile_masjid_id saat membuat jamaah.'
            ], 400);
        }

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

        return new JamaahResource(true, 'Data jamaah berhasil disimpan.', $jamaah->load(['profileMasjid', 'createdBy', 'updatedBy']));
    }

    /**
     * Menampilkan detail satu jamaah.
     */
    public function show(Jamaah $jamaah)
    {
        return new JamaahResource(true, 'Detail data jamaah', $jamaah->load(['profileMasjid', 'createdBy', 'updatedBy']));
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

        return new JamaahResource(true, 'Data jamaah berhasil diupdate.', $jamaah->load(['profileMasjid', 'createdBy', 'updatedBy']));
    }


    public function destroy(Jamaah $jamaah)
    {
        $jamaah->delete();

        return new JamaahResource(true, 'Data jamaah berhasil dihapus.', null);
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
