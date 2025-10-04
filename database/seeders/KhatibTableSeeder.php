<?php

namespace Database\Seeders;

use App\Models\Khatib;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class KhatibTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaKhatib = [
            'Ustadz Ahmad Syafi\'i',
            'KH. Muhammad Nashir',
            'Ustadz Abdul Malik',
            'KH. Fajar Siddiq',
            'Ustadz Rahman Hidayat',
            'KH. Budi Mulyono',
            'Ustadz Ibrahim Khalil',
            'KH. Dedi Setiawan',
            'Ustadz Ali Imron',
            'KH. Eko Widodo',
            'Ustadz Umar Fadhil',
            'KH. Hasan Mahmud',
            'Ustadz Uthman Nashir',
            'KH. Irfan Maulana',
            'Ustadz Abu Hurairah',
            'KH. Lukman Hakim',
            'Ustadz Saleh Abdurrahman',
            'KH. Rudi Hermawan'
        ];

        $namaIndex = 0;

        // Loop untuk setiap profile_masjid_id (1-6)
        for ($masjidId = 1; $masjidId <= 6; $masjidId++) {
            $userId = $masjidId + 1; // User ID mulai dari 2 hingga 7

            // Buat 3 khatib untuk setiap masjid
            for ($i = 0; $i < 3; $i++) {
                $nama = $namaKhatib[$namaIndex];
                $namaIndex++;

                Khatib::create([
                    'profile_masjid_id' => $masjidId,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(3),
                    'no_handphone' => '0813' . rand(10000000, 99999999),
                    'alamat' => 'Jl. ' . explode(' ', $nama)[1] . ' No. ' . rand(1, 50) . ', Yogyakarta',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
