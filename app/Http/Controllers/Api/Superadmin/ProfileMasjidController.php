<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Models\ProfileMasjid;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileMasjidResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileMasjidController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:profile-masjids.index'], only: ['index']),
            new Middleware(['permission:profile-masjids.create'], only: ['store']),
            new Middleware(['permission:profile-masjids.edit'], only: ['update']),
            new Middleware(['permission:profile-masjids.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $query = ProfileMasjid::with(['user', 'createdBy']);

            // Search functionality
            if (request()->filled('search')) {
                $query->where('nama', 'like', '%' . request()->search . '%')
                    ->orWhere('alamat', 'like', '%' . request()->search . '%');
            }

            // Filter by status
            if (request()->filled('status')) {
                if (request()->status === 'active') {
                    $query->whereHas('user', function ($q) {
                        $q->where('is_active', true);
                    });
                } elseif (request()->status === 'inactive') {
                    $query->whereHas('user', function ($q) {
                        $q->where('is_active', false);
                    });
                }
            }

            $profileMasjids = $query->latest()->paginate(10);
            $profileMasjids->appends(['search' => request()->search, 'status' => request()->status]);

            return ProfileMasjidResource::collection($profileMasjids)->additional([
                'success' => true,
                'message' => $profileMasjids->isEmpty() ? 'Belum ada data profile masjid.' : 'List Data Profile Masjid',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data profile masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id|unique:profile_masjids,user_id',
            'nama'      => 'required|string|max:255',
            'alamat'    => 'required|string|max:500',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::find($request->user_id);
            if (!$user || !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak valid atau bukan admin.'
                ], 400);
            }

            $imageName = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('photos', $imageName, 'public');
            }

            $profileMasjid = ProfileMasjid::create([
                'user_id'       => $request->user_id,
                'nama'          => $request->nama,
                'alamat'        => $request->alamat,
                'slug'          => Str::slug($request->nama),
                'image'         => $imageName,
                'created_by'    => $request->user()->id,
            ]);

            if ($profileMasjid) {
                return new ProfileMasjidResource(true, 'Data Profile Masjid Berhasil Disimpan!', $profileMasjid->load(['user', 'createdBy']));
            }

            return new ProfileMasjidResource(false, 'Data Profile Masjid Gagal Disimpan!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data profile masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $profileMasjid = ProfileMasjid::with(['user', 'createdBy'])->find($id);

            if ($profileMasjid) {
                return new ProfileMasjidResource(true, 'Detail Data Profile Masjid!', $profileMasjid);
            }

            return new ProfileMasjidResource(false, 'Detail Data Profile Masjid Tidak Ditemukan!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail profile masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'   => 'required|exists:users,id|unique:profile_masjids,user_id,' . $id,
            'nama'      => 'required|string|max:255',
            'alamat'    => 'required|string|max:500',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $profileMasjid = ProfileMasjid::find($id);
            if (!$profileMasjid) {
                return new ProfileMasjidResource(false, 'Profile Masjid Tidak Ditemukan!', null);
            }

            $user = User::find($request->user_id);
            if (!$user || !$user->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak valid atau bukan admin.'
                ], 400);
            }

            $updateData = [
                'user_id'       => $request->user_id,
                'nama'          => $request->nama,
                'alamat'        => $request->alamat,
                'slug'          => Str::slug($request->nama),
                'updated_by'    => $request->user()->id,
            ];

            if ($request->hasFile('image')) {
                // Hapus image lama
                if ($profileMasjid->image) {
                    Storage::disk('public')->delete('photos/' . $profileMasjid->image);
                }

                // Upload image baru
                $image = $request->file('image');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('photos', $imageName, 'public');
                $updateData['image'] = $imageName;
            }

            $profileMasjid->update($updateData);

            if ($profileMasjid) {
                return new ProfileMasjidResource(true, 'Data Profile Masjid Berhasil Diupdate!', $profileMasjid->load(['user', 'createdBy']));
            }

            return new ProfileMasjidResource(false, 'Data Profile Masjid Gagal Diupdate!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data profile masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $profileMasjid = ProfileMasjid::find($id);
            if (!$profileMasjid) {
                return new ProfileMasjidResource(false, 'Profile Masjid Tidak Ditemukan!', null);
            }

            if ($profileMasjid->image) {
                Storage::disk('public')->delete('photos/' . $profileMasjid->image);
            }

            if ($profileMasjid->delete()) {
                return new ProfileMasjidResource(true, 'Data Profile Masjid Berhasil Dihapus!', null);
            }

            return new ProfileMasjidResource(false, 'Data Profile Masjid Gagal Dihapus!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data profile masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update active status of profile masjid admin
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $profileMasjid = ProfileMasjid::with('user')->find($id);
            if (!$profileMasjid || !$profileMasjid->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile Masjid atau User tidak ditemukan!',
                ], 404);
            }

            // Update the is_active status di tabel users
            $profileMasjid->user->update([
                'is_active' => $request->is_active,
            ]);

            // Update semua takmir yang terhubung dengan masjid ini
            $takmirs = \App\Models\Takmir::where('profile_masjid_id', $profileMasjid->id)->get();
            foreach ($takmirs as $takmir) {
                if ($takmir->user) {
                    $takmir->user->update([
                        'is_active' => $request->is_active,
                    ]);
                }
            }

            // Reload relationships
            $profileMasjid->load('user');

            $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return (new ProfileMasjidResource($profileMasjid))->additional([
                'success' => true,
                'message' => "Admin masjid berhasil {$status}!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status admin masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status of profile masjid admin (legacy method for backward compatibility)
     */
    public function toggleActive($id)
    {
        try {
            $profileMasjid = ProfileMasjid::with('user')->find($id);
            if (!$profileMasjid || !$profileMasjid->user) {
                return new ProfileMasjidResource(false, 'Profile Masjid atau User tidak ditemukan!', null);
            }

            // Toggle the is_active status
            $profileMasjid->user->update([
                'is_active' => !$profileMasjid->user->is_active,
                'updated_by' => request()->user()->id
            ]);

            // Update audit trail di profile masjid
            $profileMasjid->update([
                'updated_by' => request()->user()->id
            ]);

            $status = $profileMasjid->user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return new ProfileMasjidResource(true, "Admin masjid berhasil {$status}!", $profileMasjid->load('user'));
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status admin masjid.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
