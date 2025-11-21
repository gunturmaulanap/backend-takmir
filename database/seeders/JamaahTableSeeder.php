<?php

namespace Database\Seeders;

use App\Models\Jamaah;
use App\Models\ProfileMasjid;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class JamaahTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masjids = ProfileMasjid::all();

        $namaTemplates = [
            'Ahmad Santoso', 'Budi Hartono', 'Citra Dewi', 'Dina Lestari', 'Eko Prasetyo',
            'Fajar Kurniawan', 'Gita Cahyani', 'Hasan Basri', 'Indra Setiawan', 'Joko Susilo',
            'Siti Aminah', 'Rudi Haryanto', 'Wulan Sari', 'Bayu Firmansyah', 'Nina Oktaviani',
            'Andi Saputra', 'Rina Agustin', 'Irfan Maulana', 'Dewi Susanti', 'Lukman Hakim',
            'Kartika Dwi', 'Mochamad Rizki', 'Eka Fitriani', 'Fadil Pratama', 'Yulia Indah',
            'Reza Pahlevi', 'Anisa Rahma', 'Pramudya Agung', 'Dyah Ayu', 'Fikri Haikal',
            'Dendi Wijaya', 'Ratna Komala', 'Dimas Prasetya', 'Shinta Puspita', 'Yoga Pratama'
        ];

        $aktivitasJamaah = [
            'Sholat Jumat', 'TPQ', 'Pengajian Rutin', 'Kegiatan Sosial',
            'Kegiatan Keagamaan', 'Relawan Masjid'
        ];

        $femaleNames = [
            'Citra Dewi', 'Dina Lestari', 'Gita Cahyani', 'Siti Aminah', 'Wulan Sari',
            'Nina Oktaviani', 'Rina Agustin', 'Dewi Susanti', 'Kartika Dwi', 'Eka Fitriani',
            'Yulia Indah', 'Anisa Rahma', 'Dyah Ayu', 'Ratna Komala', 'Shinta Puspita'
        ];

        foreach ($masjids as $masjid) {
            $userId = $masjid->user_id;
            $usedNames = []; // Track nama yang sudah dipakai untuk masjid ini

            // Buat 5 data jamaah untuk setiap masjid
            for ($i = 0; $i < 5; $i++) {
                // Pilih nama yang belum dipakai di masjid ini
                $availableNames = array_diff($namaTemplates, $usedNames);

                // Jika semua nama sudah dipakai, reset array
                if (empty($availableNames)) {
                    $usedNames = [];
                    $availableNames = $namaTemplates;
                }

                $nama = $availableNames[array_rand($availableNames)];
                $usedNames[] = $nama;

                $gender = in_array($nama, $femaleNames) ? 'Perempuan' : 'Laki-laki';
                $umur = rand(15, 60);

                Jamaah::create([
                    'profile_masjid_id' => $masjid->id,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(5),
                    'no_handphone' => '081' . rand(100000000, 999999999),
                    'alamat' => $this->generateRandomAddress(),
                    'umur' => $umur,
                    'jenis_kelamin' => $gender,
                    'aktivitas_jamaah' => $aktivitasJamaah[array_rand($aktivitasJamaah)],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }

    /**
     * Generate random address for jamaah
     */
    private function generateRandomAddress(): string
    {
        $streets = [
            'KH. Ahmad Dahlan', 'Sultan Agung', 'Malioboro', 'Kaliurang', 'Gejayan',
            'Urip Sumoharjo', 'Bantul', 'Sleman', 'Kaliurang Km', 'Palagan'
        ];

        $cities = [
            'Yogyakarta', 'Bantul', 'Sleman', 'Godean', 'Depok', 'Berbah'
        ];

        $street = $streets[array_rand($streets)];
        $city = $cities[array_rand($cities)];
        $number = rand(1, 100);

        return "Jl. {$street} No. {$number}, {$city}";
    }
}
