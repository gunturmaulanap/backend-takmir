<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Imam;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreImamRequest;
use App\Http\Requests\UpdateImamRequest;
use App\Http\Resources\ImamResource;
use Illuminate\Support\Facades\DB;
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

        $query = Imam::with(['profileMasjid', 'createdBy', 'updatedBy'])
            ->where('profile_masjid_id', $profileMasjidId);

        // Filter berdasarkan nama
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        // Filter berdasarkan status aktif
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $imams = $query->latest()->paginate(10);

        return response()->json(
            ImamResource::customResponse(true, 'List Data Imam', ImamResource::collection($imams))
        );
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
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'is_active' => $validated['is_active'] ?? true,
            ...array_diff_key($validated, array_flip(['is_active']))
        ]);

        return response()->json(
            ImamResource::customResponse(true, 'Data imam berhasil disimpan.', new ImamResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function show(Imam $imam)
    {
        return response()->json(
            ImamResource::customResponse(true, 'Detail data imam berhasil dimuat.', new ImamResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function update(UpdateImamRequest $request, Imam $imam)
    {
        $validated = $request->validated();
        $user = $request->user();

        $imam->update([
            'updated_by' => $user->id,
            ...$validated
        ]);

        return response()->json(
            ImamResource::customResponse(true, 'Data imam berhasil diupdate.', new ImamResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])))
        );
    }

    public function destroy(Imam $imam)
    {
        $imam->delete();

        return response()->json(
            ImamResource::customResponse(true, 'Data imam berhasil dihapus.', null)
        );
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

        return response()->json(
            ImamResource::customResponse(true, 'Status imam berhasil diperbarui!', new ImamResource($imam->load(['profileMasjid', 'createdBy', 'updatedBy'])))
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
