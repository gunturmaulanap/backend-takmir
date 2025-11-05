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
            $query = User::role('admin')->with(['profileMasjid', 'roles']);

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

            if ($admins->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada data admin.',
                    'data' => []
                ], 200);
            }

            return new AdminResource(true, 'List Data Admin Masjid', $admins);
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
                'created_by'    => $request->user()->id,
            ]);

            // Assign admin role
            $user->assignRole('admin');

            // If profile_masjid_id is provided, link to existing profile
            if ($request->filled('profile_masjid_id')) {
                $profileMasjid = ProfileMasjid::find($request->profile_masjid_id);
                if ($profileMasjid && !$profileMasjid->user_id) {
                    $profileMasjid->update([
                        'user_id' => $user->id,
                        'updated_by' => $request->user()->id
                    ]);
                }
            }

            if ($user) {
                return new AdminResource(true, 'Data Admin Berhasil Disimpan!', $user->load(['profileMasjid', 'roles']));
            }

            return new AdminResource(false, 'Data Admin Gagal Disimpan!', null);
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
            $admin = User::role('admin')->with(['profileMasjid', 'roles'])->find($id);

            if ($admin) {
                return new AdminResource(true, 'Detail Data Admin!', $admin);
            }

            return new AdminResource(false, 'Detail Data Admin Tidak Ditemukan!', null);
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
            $admin = User::role('admin')->find($id);
            if (!$admin) {
                return new AdminResource(false, 'Admin Tidak Ditemukan!', null);
            }

            $updateData = [
                'name'          => $request->name,
                'email'         => $request->email,
                'username'      => $request->username,
                'updated_by'    => $request->user()->id,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $admin->update($updateData);

            // Handle profile masjid assignment
            if ($request->filled('profile_masjid_id')) {
                // Remove from old profile if exists
                if ($admin->profileMasjid) {
                    $admin->profileMasjid->update([
                        'user_id' => null,
                        'updated_by' => $request->user()->id
                    ]);
                }

                // Assign to new profile
                $profileMasjid = ProfileMasjid::find($request->profile_masjid_id);
                if ($profileMasjid) {
                    $profileMasjid->update([
                        'user_id' => $admin->id,
                        'updated_by' => $request->user()->id
                    ]);
                }
            } else {
                // Remove from current profile if profile_masjid_id is null
                if ($admin->profileMasjid) {
                    $admin->profileMasjid->update([
                        'user_id' => null,
                        'updated_by' => $request->user()->id
                    ]);
                }
            }

            if ($admin) {
                return new AdminResource(true, 'Data Admin Berhasil Diupdate!', $admin->load(['profileMasjid', 'roles']));
            }

            return new AdminResource(false, 'Data Admin Gagal Diupdate!', null);
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
            $admin = User::role('admin')->find($id);
            if (!$admin) {
                return new AdminResource(false, 'Admin Tidak Ditemukan!', null);
            }

            // Remove from profile masjid if linked
            if ($admin->profileMasjid) {
                $admin->profileMasjid->update([
                    'user_id' => null,
                    'updated_by' => request()->user()->id
                ]);
            }

            if ($admin->delete()) {
                return new AdminResource(true, 'Data Admin Berhasil Dihapus!', null);
            }

            return new AdminResource(false, 'Data Admin Gagal Dihapus!', null);
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
            $admin = User::role('admin')->find($id);
            if (!$admin) {
                return new AdminResource(false, 'Admin Tidak Ditemukan!', null);
            }

            // Update the is_active status
            $admin->update([
                'is_active' => $request->is_active,
                'updated_by' => $request->user()->id
            ]);

            $status = $request->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return new AdminResource(true, "Admin berhasil {$status}!", $admin->load(['profileMasjid', 'roles']));
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
            $admin = User::role('admin')->find($id);
            if (!$admin) {
                return new AdminResource(false, 'Admin Tidak Ditemukan!', null);
            }

            // Toggle the is_active status
            $admin->update([
                'is_active' => !$admin->is_active,
                'updated_by' => request()->user()->id
            ]);

            $status = $admin->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return new AdminResource(true, "Admin berhasil {$status}!", $admin->load(['profileMasjid', 'roles']));
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
            $admins = User::role('admin')
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