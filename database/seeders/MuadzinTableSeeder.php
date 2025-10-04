<?php

namespace Database\Seeders;

use App\Models\Muadzin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MuadzinTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $namaMuadzin = [
            'Ahmad Fauzi',
            'Muhammad Yusuf',
            'Abdul Aziz',
            'Fajar Rahman',
            'Rahman Hidayat',
            'Budi Santoso',
            'Ibrahim Maulana',
            'Dedi Kurniawan',
            'Ali Mustafa',
            'Eko Saputra',
            'Umar Fadhil',
            'Hasan Basri',
            'Uthman Nashir',
            'Irfan Hakim',
            'Abu Bakar',
            'Lukman Arif',
            'Saleh Rahman',
            'Rudi Hartono'
        ];

        $tugasMuadzin = [
            'Adzan Subuh',
            'Adzan Dhuhur',
            'Adzan Ashar',
            'Adzan Maghrib',
            'Adzan Isya',
            'Adzan Jumat'
        ];

        $namaIndex = 0;

        // Loop untuk setiap profile_masjid_id (1-6)
        for ($masjidId = 1; $masjidId <= 6; $masjidId++) {
            $userId = $masjidId + 1; // User ID mulai dari 2 hingga 7

            // Buat 3 muadzin untuk setiap masjid
            for ($i = 0; $i < 3; $i++) {
                $nama = $namaMuadzin[$namaIndex];
                $namaIndex++;

                Muadzin::create([
                    'profile_masjid_id' => $masjidId,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(3),
                    'no_handphone' => '0814' . rand(10000000, 99999999),
                    'alamat' => 'Jl. ' . explode(' ', $nama)[0] . ' No. ' . rand(1, 50) . ', Yogyakarta',
                    'tugas' => $tugasMuadzin[array_rand($tugasMuadzin)],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
