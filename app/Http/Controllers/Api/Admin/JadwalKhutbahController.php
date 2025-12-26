<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\JadwalKhutbah;
use App\Models\Imam;
use App\Models\Khatib;
use App\Models\Muadzin;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalKhutbahRequest;
use App\Http\Requests\UpdateJadwalKhutbahRequest;
use App\Http\Resources\JadwalKhutbahResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class JadwalKhutbahController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['permission:jadwal-petugas.index'], only: ['index']),
            new Middleware(['permission:jadwal-petugas.create'], only: ['store']),
            new Middleware(['permission:jadwal-petugas.edit'], only: ['update']),
            new Middleware(['permission:jadwal-petugas.delete'], only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $query = JadwalKhutbah::with(['profileMasjid', 'createdBy', 'updatedBy', 'imam', 'khatib', 'muadzin']);

        $jadwalKhutbah = $query->orderBy('tanggal', 'desc')->paginate(15);

        // Return consistent pagination response
        return response()->json([
            'success' => true,
            'message' => 'List Data Jadwal Khutbah',
            'data' => $jadwalKhutbah->items(),
            'meta' => [
                'current_page' => $jadwalKhutbah->currentPage(),
                'from' => $jadwalKhutbah->firstItem(),
                'last_page' => $jadwalKhutbah->lastPage(),
                'per_page' => $jadwalKhutbah->perPage(),
                'to' => $jadwalKhutbah->lastItem(),
                'total' => $jadwalKhutbah->total(),
            ]
        ]);
    }

    public function store(StoreJadwalKhutbahRequest $request)
    {
        try {
            $validated = $request->validated();

            // Ambil user dan profil masjid langsung dari request (seperti CategoryController & EventController)
            $user = $request->user();
            $masjidProfile = $user->getMasjidProfile();

            if (!$masjidProfile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profile masjid tidak ditemukan.'
                ], 400);
            }

            // Cek apakah sudah ada jadwal di tanggal yang sama
            $existingSchedule = JadwalKhutbah::where('profile_masjid_id', $masjidProfile->id)
                ->where('tanggal', $validated['tanggal'])
                ->first();

            if ($existingSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal khutbah untuk tanggal ini sudah ada.'
                ], 400);
            }

            $jadwalKhutbah = JadwalKhutbah::create(array_merge($validated, [
                'profile_masjid_id' => $masjidProfile->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]));

            // Load relationships untuk response
            $jadwalKhutbah->load(['profileMasjid', 'imam', 'khatib', 'muadzin', 'createdBy', 'updatedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal khutbah berhasil disimpan.',
                'data' => new JadwalKhutbahResource($jadwalKhutbah)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data jadwal khutbah.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(JadwalKhutbah $jadwalKhutbah)
    {
        $jadwalKhutbah->load(['profileMasjid', 'imam', 'khatib', 'muadzin', 'createdBy', 'updatedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Detail data jadwal khutbah berhasil dimuat.',
            'data' => $jadwalKhutbah
        ]);
    }

    public function update(UpdateJadwalKhutbahRequest $request, JadwalKhutbah $jadwalKhutbah)
    {
        try {
            $validated = $request->validated();

            // Prepare data untuk update
            $updateData = [
                'updated_by' => $request->user()->id,
            ];

            // Cek apakah sudah ada jadwal di tanggal yang sama (kecuali jadwal ini sendiri)
            $existingSchedule = JadwalKhutbah::where('profile_masjid_id', $jadwalKhutbah->profile_masjid_id)
                ->where('tanggal', $validated['tanggal'])
                ->where('id', '!=', $jadwalKhutbah->id)
                ->first();

            if ($existingSchedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal khutbah untuk tanggal ini sudah ada.'
                ], 400);
            }

            // Merge dengan validated data
            $jadwalKhutbah->update(array_merge($validated, $updateData));

            // Reload jadwal khutbah untuk mendapatkan data terbaru termasuk slug baru
            $jadwalKhutbah->refresh();
            $jadwalKhutbah->load(['profileMasjid', 'imam', 'khatib', 'muadzin', 'createdBy', 'updatedBy']);

            return response()->json([
                'success' => true,
                'message' => 'Data jadwal khutbah berhasil diperbarui.',
                'data' => $jadwalKhutbah
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
                'message' => 'Terjadi kesalahan saat memperbarui data jadwal khutbah.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(JadwalKhutbah $jadwalKhutbah)
    {
        $jadwalKhutbah->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal khutbah berhasil dihapus.',
            'data' => null
        ]);
    }

    /**
     * Get jadwal khutbah untuk calendar view
     */
    public function calendar(Request $request)
    {
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        $year = $request->get('year', Carbon::now()->year);
        $month = $request->get('month', Carbon::now()->month);

        $jadwalKhutbah = JadwalKhutbah::with(['imam', 'khatib', 'muadzin'])
            ->where('profile_masjid_id', $profileMasjidId)
            ->whereYear('tanggal', $year)
            ->whereMonth('tanggal', $month)
            ->orderBy('tanggal')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $jadwalKhutbah->map(function ($jadwal) {
                return [
                    'id' => $jadwal->id,
                    'title' => 'Khutbah Jumat',
                    'date' => $jadwal->tanggal->format('Y-m-d'),
                    'imam' => $jadwal->imam->nama,
                    'khatib' => $jadwal->khatib->nama,
                    'muadzin' => $jadwal->muadzin->nama,
                    'tema_khutbah' => $jadwal->tema_khutbah,
                ];
            })
        ]);
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
