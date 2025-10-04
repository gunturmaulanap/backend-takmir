<?php

namespace Database\Seeders;

use App\Models\JadwalKhutbah;
use Illuminate\Database\Seeder;

class JadwalKhutbahTableSeeder extends Seeder
{
    public function run(): void
    {
        $jumlahMasjid = 6;
        $jumlahJadwalPerMasjid = 3;
        $userId = 1; // Superadmin
        $tanggalAwal = strtotime('2025-01-03');
        $hari = 'Jumat';

        for ($masjidId = 1; $masjidId <= $jumlahMasjid; $masjidId++) {
            // Ambil id imam, khatib, muadzin untuk masjid ini (diasumsikan urut per masjid, 3 per masjid)
            $imamStart = ($masjidId - 1) * 3 + 1;
            $khatibStart = ($masjidId - 1) * 3 + 1;
            $muadzinStart = ($masjidId - 1) * 3 + 1;
            for ($i = 0; $i < $jumlahJadwalPerMasjid; $i++) {
                $tanggal = date('Y-m-d', strtotime("+" . ($i + ($masjidId - 1) * $jumlahJadwalPerMasjid) * 7 . " days", $tanggalAwal));
                JadwalKhutbah::create([
                    'tanggal' => $tanggal,
                    'hari' => $hari,
                    'imam_id' => $imamStart + $i,
                    'khatib_id' => $khatibStart + $i,
                    'muadzin_id' => $muadzinStart + $i,
                    'profile_masjid_id' => $masjidId,
                    'catatan' => 'Khutbah ke-' . ($i + 1) . ' di masjid ' . $masjidId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
