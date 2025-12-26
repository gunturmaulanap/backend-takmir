<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

/**
 * @method \App\Models\User user()
 */
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
     * @method \App\Models\User user()
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255|unique:categories,nama,NULL,id,profile_masjid_id,' . $this->user()->getMasjidProfile()->id,
            'warna' => 'nullable|string|in:Blue,Green,Purple,Orange,Indigo',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            $profileMasjidId = $this->getProfileMasjidId($user, $request);



            $query = Category::query();

            // Filter berdasarkan profile masjid jika bukan superadmin
            if (!$user->roles->contains('name', 'superadmin') && $profileMasjidId) {
                $query->where('profile_masjid_id', $profileMasjidId);
                Log::info("Applied profile_masjid_id filter: " . $profileMasjidId);
            }

            // Search functionality
            if ($request->filled('search')) {
                $query->where('nama', 'like', '%' . $request->search . '%');
            }

            $categories = $query->latest()->paginate(6);

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
     * Menyimpan category baru.
     */
    public function store(StoreCategoryRequest $request)
    {
        try {
            $validated = $request->validated();

            // Ambil user dan profil masjid langsung dari request
            $user = $request->user();
            $masjidProfile = $user->getMasjidProfile();

            $category = Category::create(array_merge($validated, [
                'profile_masjid_id' => $masjidProfile->id,
                'created_by'        => $user->id,
                'updated_by'        => $user->id,
                'slug'              => Str::slug($validated['nama']),
            ]));

            return new CategoryResource(true, 'Data category berhasil disimpan.', $category);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
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
     * Memperbarui category.
     */
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            $validated = $request->validated();

            // Prepare data untuk update
            $updateData = [
                'slug'       => Str::slug($validated['nama']),
                'updated_by' => $request->user()->id,
            ];

            // Merge dengan validated data
            $category->update(array_merge($validated, $updateData));

            // Reload category untuk mendapatkan data terbaru termasuk slug baru
            $category->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Data category berhasil diperbarui.',
                'data' => $category,
                'meta' => [
                    'new_slug' => $category->slug,
                    'old_slug_updated' => true
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
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

            $categories = $query->select('id', 'nama', 'warna')->get();

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
