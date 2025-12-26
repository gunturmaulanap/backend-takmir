<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Models\User;
use App\Models\ProfileMasjid;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:admins.index'], only: ['index']),
            new Middleware(['permission:admins.create'], only: ['store']),
            new Middleware(['permission:admins.edit'], only: ['update']),
            new Middleware(['permission:admins.delete'], only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $query = User::role('admin', 'api')->with(['profileMasjid', 'roles']);

            // Search functionality
            if (request()->filled('search')) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . request()->search . '%')
                      ->orWhere('email', 'like', '%' . request()->search . '%');
                });
            }

            // Filter by status
            if (request()->filled('status')) {
                if (request()->status === 'active') {
                    $query->where('is_active', true);
                } elseif (request()->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Filter by profile masjid
            if (request()->filled('profile_masjid_id')) {
                $query->whereHas('profileMasjid', function ($q) {
                    $q->where('id', request()->profile_masjid_id);
                });
            }

            $admins = $query->latest()->paginate(10);
            $admins->appends([
                'search' => request()->search,
                'status' => request()->status,
                'profile_masjid_id' => request()->profile_masjid_id
            ]);

            return AdminResource::collection($admins)->additional([
                'success' => true,
                'message' => $admins->isEmpty() ? 'Belum ada data admin.' : 'List Data Admin Masjid',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data admin.',
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'username'      => 'required|string|max:50|unique:users,username',
            'profile_masjid_id' => 'nullable|exists:profile_masjids,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Get admin role
            $adminRole = Role::where('name', 'admin')->first();
            if (!$adminRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role admin tidak ditemukan.'
                ], 400);
            }

            // Create user with admin role
            $user = User::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => Hash::make($request->password),
                'is_active'     => true, // Default active
            ]);

            // Assign admin role
            $user->assignRole('admin');

            // If profile_masjid_id is provided, link to existing profile
            if ($request->filled('profile_masjid_id')) {
                $profileMasjid = ProfileMasjid::find($request->profile_masjid_id);
                if ($profileMasjid && !$profileMasjid->user_id) {
                    $profileMasjid->update([
                        'user_id' => $user->id,
                    ]);
                }
            }

            return (new AdminResource($user->load(['profileMasjid', 'roles'])))->additional([
                'success' => true,
                'message' => 'Data Admin Berhasil Disimpan!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data admin.',
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
            $admin = User::role('admin', 'api')->with(['profileMasjid', 'roles'])->find($id);

            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Detail Data Admin Tidak Ditemukan!',
                ], 404);
            }

            return (new AdminResource($admin))->additional([
                'success' => true,
                'message' => 'Detail Data Admin!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail admin.',
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
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email,' . $id,
            'username'      => 'required|string|max:50|unique:users,username,' . $id,
            'password'      => 'nullable|string|min:8|confirmed',
            'profile_masjid_id' => 'nullable|exists:profile_masjids,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $admin = User::role('admin', 'api')->find($id);
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin Tidak Ditemukan!',
                ], 404);
            }

            $updateData = [
                'name'          => $request->name,
                'email'         => $request->email,
                'username'      => $request->username,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $admin->update($updateData);

            // Hanya handle profile masjid assignment jika field diisi secara eksplisit
            // Jangan menghapus assignment yang sudah ada
            if ($request->has('profile_masjid_id') && $request->filled('profile_masjid_id')) {
                // Remove from old profile if exists and different
                if ($admin->profileMasjid && $admin->profileMasjid->id != $request->profile_masjid_id) {
                    $admin->profileMasjid->update([
                        'user_id' => null,
                    ]);
                }

                // Assign to new profile
                $profileMasjid = ProfileMasjid::find($request->profile_masjid_id);
                if ($profileMasjid) {
                    $profileMasjid->update([
                        'user_id' => $admin->id,
                    ]);
                }
            }

            return (new AdminResource($admin->load(['profileMasjid', 'roles'])))->additional([
                'success' => true,
                'message' => 'Data Admin Berhasil Diupdate!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data admin.',
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
            $admin = User::role('admin', 'api')->find($id);
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin Tidak Ditemukan!',
                ], 404);
            }

            // Remove from profile masjid if linked
            if ($admin->profileMasjid) {
                $admin->profileMasjid->update([
                    'user_id' => null,
                ]);
            }

            if ($admin->delete()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data Admin Berhasil Dihapus!',
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data Admin Gagal Dihapus!',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data admin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update active status of admin
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $admin = User::role('admin', 'api')->find($id);
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin Tidak Ditemukan!',
                ], 404);
            }

            // Update the is_active status
            $admin->update([
                'is_active' => $request->is_active,
            ]);

            // Jika admin memiliki profile masjid, update semua takmir yang terhubung
            if ($admin->profileMasjid) {
                $takmirs = \App\Models\Takmir::where('profile_masjid_id', $admin->profileMasjid->id)->get();
                foreach ($takmirs as $takmir) {
                    if ($takmir->user) {
                        $takmir->user->update([
                            'is_active' => $request->is_active,
                        ]);
                    }
                }
            }

            $admin->load(['profileMasjid', 'roles']);

            $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return (new AdminResource($admin))->additional([
                'success' => true,
                'message' => "Admin berhasil {$status}!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status admin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle active status of admin (legacy method for backward compatibility)
     */
    public function toggleActive($id)
    {
        try {
            $admin = User::role('admin', 'api')->find($id);
            if (!$admin) {
                return response()->json([
                    'success' => false,
                    'message' => 'Admin Tidak Ditemukan!',
                ], 404);
            }

            // Toggle the is_active status
            $newStatus = !$admin->is_active;
            $admin->update([
                'is_active' => $newStatus,
            ]);

            // Jika admin memiliki profile masjid, update semua takmir yang terhubung
            if ($admin->profileMasjid) {
                $takmirs = \App\Models\Takmir::where('profile_masjid_id', $admin->profileMasjid->id)->get();
                foreach ($takmirs as $takmir) {
                    if ($takmir->user) {
                        $takmir->user->update([
                            'is_active' => $newStatus,
                        ]);
                    }
                }
            }

            $status = $newStatus ? 'diaktifkan' : 'dinonaktifkan';

            return (new AdminResource($admin->load(['profileMasjid', 'roles'])))->additional([
                'success' => true,
                'message' => "Admin berhasil {$status}!",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengubah status admin.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admins without profile masjid
     */
    public function unassigned()
    {
        try {
            $admins = User::role('admin', 'api')
                ->whereDoesntHave('profileMasjid')
                ->where('is_active', true)
                ->select('id', 'name', 'email')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'List admin yang belum memiliki profile masjid',
                'data' => $admins
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data admin unassigned.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}