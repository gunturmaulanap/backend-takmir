<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller implements HasMiddleware
{
    /**
     * middleware
     *
     * @return array
     */
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:categories.index'], only: ['index']),
            new Middleware(['permission:categories.create'], only: ['store']),
            new Middleware(['permission:categories.edit'], only: ['update']),
            new Middleware(['permission:categories.delete'], only: ['destroy']),
        ];
    }

    /**
     * Get profile masjid ID based on user role
     */
    private function getProfileMasjidId($user, $request = null)
    {
        if (!$user) {
            return null;
        }

        // Jika superadmin dan ada profile_masjid_id di request, gunakan itu
        if ($user->roles->contains('name', 'superadmin') && $request && $request->filled('profile_masjid_id')) {
            return $request->profile_masjid_id;
        }

        // Jika admin atau takmir, ambil dari profile user
        if ($user->roles->contains('name', 'admin') || $user->roles->contains('name', 'takmir')) {
            $profileMasjid = $user->getMasjidProfile();
            return $profileMasjid ? $profileMasjid->id : null;
        }

        // Jika superadmin tanpa profile_masjid_id di request, ambil yang pertama
        if ($user->roles->contains('name', 'superadmin')) {
            $firstProfile = \App\Models\ProfileMasjid::first();
            return $firstProfile ? $firstProfile->id : null;
        }

        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $profileMasjidId = $this->getProfileMasjidId($user, $request);

            // Debug: Log user info
            Log::info("CategoryController Debug", [
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name'),
                'profile_masjid_id' => $profileMasjidId,
                'is_superadmin' => $user->roles->contains('name', 'superadmin')
            ]);

            $query = Category::query();

            // Filter berdasarkan profile masjid jika bukan superadmin
            if (!$user->roles->contains('name', 'superadmin') && $profileMasjidId) {
                $query->where('profile_masjid_id', $profileMasjidId);
                Log::info("Applied profile_masjid_id filter: " . $profileMasjidId);
            }

            // Search functionality
            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->search . '%');
            }

            $categories = $query->latest()->paginate(10);

            $categories->appends(['search' => $request->search]);

            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Belum ada data kategori.',
                    'data' => []
                ], 200);
            }

            return new CategoryResource(true, 'List Data Categories', $categories);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data kategori.',
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
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|in:Blue,Green,Purple,Orange,Indigo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi.'
                ], 403);
            }

            // Ambil profile_masjid_id berdasarkan role user
            $profileMasjidId = $this->getProfileMasjidId($user, $request);

            if (!$profileMasjidId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile masjid tidak ditemukan.'
                ], 400);
            }

            // Validasi color jika ada
            $color = $request->color ?? 'Blue';

            //create category dengan audit columns dan profile_masjid_id
            $category = Category::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),
                'color' => $color,
                'profile_masjid_id' => $profileMasjidId,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);

            if ($category) {
                return new CategoryResource(true, 'Data Category Berhasil Disimpan!', $category);
            }

            return new CategoryResource(false, 'Data Category Gagal Disimpan!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        try {
            return new CategoryResource(true, 'Detail Data Category!', $category);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil detail kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|in:Blue,Green,Purple,Orange,Indigo',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi.'
                ], 403);
            }

            $category->update([
                'name' => $request->name,
                'slug' => Str::slug($request->name, '-'),
                'color' => $request->color ?? $category->color,
                'updated_by' => $user->id,
            ]);

            if ($category) {
                return new CategoryResource(true, 'Data Category Berhasil Diupdate!', $category);
            }

            return new CategoryResource(false, 'Data Category Gagal Diupdate!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        try {
            if ($category->delete()) {
                return new CategoryResource(true, 'Data Category Berhasil Dihapus!', null);
            }

            return new CategoryResource(false, 'Data Category Gagal Dihapus!', null);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all categories for dropdown/select options
     */
    public function all(Request $request)
    {
        try {
            $user = Auth::user();
            $profileMasjidId = $this->getProfileMasjidId($user, $request);

            // Debug: Log user info for all method
            Log::info("CategoryController All Method Debug", [
                'user_id' => $user->id,
                'user_roles' => $user->roles->pluck('name'),
                'profile_masjid_id' => $profileMasjidId,
                'is_superadmin' => $user->roles->contains('name', 'superadmin')
            ]);

            $query = Category::query();

            // Filter berdasarkan profile masjid jika bukan superadmin
            if (!$user->roles->contains('name', 'superadmin') && $profileMasjidId) {
                $query->where('profile_masjid_id', $profileMasjidId);
                Log::info("Applied profile_masjid_id filter in all method: " . $profileMasjidId);
            }

            $categories = $query->select('id', 'name', 'color')->get();

            Log::info("Categories found: " . $categories->count());

            if ($categories->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kategori tidak ditemukan.'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'List semua kategori berhasil dimuat.',
                'data' => $categories
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil semua kategori.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
