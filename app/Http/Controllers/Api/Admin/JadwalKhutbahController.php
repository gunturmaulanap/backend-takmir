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
        $validated = $request->validated();
        $user = $request->user();
        $profileMasjidId = $this->getProfileMasjidId($user, $request);

        if (!$profileMasjidId) {
            return response()->json([
                'success' => false,
                'message' => 'Profile masjid tidak ditemukan.'
            ], 400);
        }

        // Validasi apakah imam, khatib, muadzin ada dan aktif di masjid ini
        $validationResult = $this->validatePersonnel($profileMasjidId, $validated);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

        // Cek apakah sudah ada jadwal di tanggal yang sama
        $existingSchedule = JadwalKhutbah::where('profile_masjid_id', $profileMasjidId)
            ->where('tanggal', $validated['tanggal'])
            ->first();

        if ($existingSchedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal khutbah untuk tanggal ini sudah ada.'
            ], 400);
        }

        $jadwalKhutbah = JadwalKhutbah::create([
            'profile_masjid_id' => $profileMasjidId,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            ...$validated
        ]);

        // Load relationships untuk response
        $jadwalKhutbah->load(['profileMasjid', 'imam', 'khatib', 'muadzin', 'createdBy', 'updatedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal khutbah berhasil disimpan.',
            'data' => $jadwalKhutbah
        ], 201);
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
        $validated = $request->validated();
        $user = $request->user();

        // Validasi apakah imam, khatib, muadzin ada dan aktif di masjid ini
        $validationResult = $this->validatePersonnel($jadwalKhutbah->profile_masjid_id, $validated);
        if (!$validationResult['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validationResult['message']
            ], 400);
        }

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

        $jadwalKhutbah->update([
            'updated_by' => $user->id,
            ...$validated
        ]);

        // Load relationships untuk response
        $jadwalKhutbah->load(['profileMasjid', 'imam', 'khatib', 'muadzin', 'createdBy', 'updatedBy']);

        return response()->json([
            'success' => true,
            'message' => 'Data jadwal khutbah berhasil diupdate.',
            'data' => $jadwalKhutbah
        ]);
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
     * Validasi personnel (imam, khatib, muadzin) di masjid
     */
    private function validatePersonnel($profileMasjidId, $data)
    {
        // Validasi Imam
        if (isset($data['imam_id'])) {
            $imam = Imam::where('id', $data['imam_id'])
                ->where('profile_masjid_id', $profileMasjidId)
                ->first();
            if (!$imam) {
                return ['valid' => false, 'message' => 'Imam tidak ditemukan atau tidak aktif di masjid ini.'];
            }
        }

        // Validasi Khatib
        if (isset($data['khatib_id'])) {
            $khatib = Khatib::where('id', $data['khatib_id'])
                ->where('profile_masjid_id', $profileMasjidId)
                ->first();
            if (!$khatib) {
                return ['valid' => false, 'message' => 'Khatib tidak ditemukan atau tidak aktif di masjid ini.'];
            }
        }

        // Validasi Muadzin
        if (isset($data['muadzin_id'])) {
            $muadzin = Muadzin::where('id', $data['muadzin_id'])
                ->where('profile_masjid_id', $profileMasjidId)
                ->first();
            if (!$muadzin) {
                return ['valid' => false, 'message' => 'Muadzin tidak ditemukan atau tidak aktif di masjid ini.'];
            }
        }

        return ['valid' => true, 'message' => ''];
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
