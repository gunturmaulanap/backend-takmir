<?php

namespace Database\Seeders;

use App\Models\Imam;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ImamTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaImam = [
            'KH. Ahmad Dahlan',
            'Ustadz Muhammad Ridwan',
            'KH. Abdul Rahman',
            'Ustadz Fajar Mubarok',
            'KH. Abdurrahman Wahid',
            'Ustadz Budi Santoso',
            'KH. Ibrahim Al-Makki',
            'Ustadz Dedi Kurniawan',
            'KH. Ali bin Abi Thalib',
            'Ustadz Eko Prasetyo',
            'KH. Umar Al-Faruq',
            'Ustadz Hasan Basri',
            'KH. Uthman bin Affan',
            'Ustadz Irfan Hakim',
            'KH. Abu Bakar Siddiq',
            'Ustadz Lukman Hakim',
            'KH. Salahuddin Al-Ayyubi',
            'Ustadz Rudi Hartono'
        ];

        $tugasImam = [
            'Imam Sholat Fardhu',
            'Imam Sholat Jumat',
            'Imam Sholat Tarawih',
            'Imam Sholat Ied',
            'Imam Sholat Jenazah'
        ];

        $namaIndex = 0;

        // Loop untuk setiap profile_masjid_id (1-6)
        for ($masjidId = 1; $masjidId <= 6; $masjidId++) {
            $userId = $masjidId + 1; // User ID mulai dari 2 hingga 7

            // Buat 3 imam untuk setiap masjid
            for ($i = 0; $i < 3; $i++) {
                $nama = $namaImam[$namaIndex];
                $namaIndex++;

                Imam::create([
                    'profile_masjid_id' => $masjidId,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(3),
                    'no_handphone' => '0812' . rand(10000000, 99999999),
                    'alamat' => 'Jl. ' . explode(' ', $nama)[1] . ' No. ' . rand(1, 50) . ', Yogyakarta',
                    'tugas' => $tugasImam[array_rand($tugasImam)],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
