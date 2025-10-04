<?php

namespace App\Http\Controllers\Api\Superadmin;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;

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
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            //get categories GLOBAL (tanpa scope masjid)
            $categories = Category::when(request()->search, function ($query) {
                $query->where('name', 'like', '%' . request()->search . '%');
            })->latest()->paginate(10);

            $categories->appends(['search' => request()->search]);

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
            'color' => 'nullable|string|max:50',
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
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak terautentikasi.'
                ], 403);
            }

            // Validasi color jika ada
            $color = $request->color ?? 'Blue';

            // Untuk superadmin, jika tidak ada profile_masjid_id, gunakan ID 1 sebagai default
            // atau ambil profile masjid pertama yang tersedia
            $profileMasjidId = $request->profile_masjid_id;

            if (!$profileMasjidId) {
                // Ambil profile masjid pertama sebagai default untuk kategori global
                $firstProfile = \App\Models\ProfileMasjid::first();
                if (!$firstProfile) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada profile masjid tersedia.'
                    ], 400);
                }
                $profileMasjidId = $firstProfile->id;
            }

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
    public function show($id)
    {
        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data kategori tidak ditemukan.'
                ], 404);
            }

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
            'color' => 'nullable|string|max:50',
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
                'profile_masjid_id' => $request->profile_masjid_id ?? $category->profile_masjid_id,
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
    public function all()
    {
        try {
            //get all categories GLOBAL (tanpa scope masjid untuk superadmin)
            $categories = Category::select('id', 'name', 'color')->get();

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
