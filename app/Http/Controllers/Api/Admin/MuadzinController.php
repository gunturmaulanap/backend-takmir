<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Muadzin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMuadzinRequest;
use App\Http\Requests\UpdateMuadzinRequest;
use App\Http\Resources\MuadzinResource;
use Illuminate\Support\Facades\DB;
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
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $query = Muadzin::with(['profileMasjid', 'createdBy', 'updatedBy'])
            ->where('profile_masjid_id', $profileMasjidId);

        // Filter berdasarkan nama
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan status aktif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $muadzins = $query->latest()->paginate(10);

        return response()->json(
            MuadzinResource::customResponse(true, 'List Data Muadzin', MuadzinResource::collection($muadzins))
        );
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
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_active' => $validated['is_active'] ?? true,
            ...array_diff_key($validated, array_flip(['is_active']))
        ]);

        return response()->json(
            MuadzinResource::customResponse(true, 'Data muadzin berhasil disimpan.', new MuadzinResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function show(Muadzin $muadzin)
    {
        return response()->json(
            MuadzinResource::customResponse(true, 'Detail data muadzin berhasil dimuat.', new MuadzinResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function update(UpdateMuadzinRequest $request, Muadzin $muadzin)
    {
        $validated = $request->validated();
        $user = $request->user();

        $muadzin->update([
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            MuadzinResource::customResponse(true, 'Data muadzin berhasil diupdate.', new MuadzinResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function destroy(Muadzin $muadzin)
    {
        $muadzin->delete();

        return response()->json(
            MuadzinResource::customResponse(true, 'Data muadzin berhasil dihapus.', null)
        );
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

        return response()->json(
            MuadzinResource::customResponse(true, 'Status muadzin berhasil diperbarui!', new MuadzinResource($muadzin->load(['profileMasjid', 'createdBy', 'updatedBy'])))
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
