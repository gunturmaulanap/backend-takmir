<?php

namespace Database\Seeders;

use App\Models\Asatidz;
use App\Models\Jamaah;
use App\Models\ProfileMasjid;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class AsatidzSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masjids = ProfileMasjid::all();

        $namaAsatidz = [
            'Ustadz Abdullah',
            'Ustadz Bukhari',
            'Ustadzah Fatimah',
            'Ustadz Hasan',
            'Ustadzah Aisyah',
            'Ustadz Ibrahim',
            'Ustadzah Khadijah',
            'Ustadz Mahmud',
            'Ustadzah Zainab',
            'Ustadz Umar',
        ];

        $keahlianList = [
            'Tahfidz Al-Quran',
            'Fiqih Ibadah',
            'Bahasa Arab',
            'Tajwid',
            'Akhlak',
            'Aqidah',
            'Hadits',
            'Tarikh Islam',
        ];

        $femaleNames = [
            'Ustadzah Fatimah',
            'Ustadzah Aisyah',
            'Ustadzah Khadijah',
            'Ustadzah Zainab'
        ];

        foreach ($masjids as $masjid) {
            $userId = $masjid->user_id;
            $usedNames = [];

            // Buat 3-5 asatidz untuk setiap masjid
            $numAsatidz = rand(3, 5);

            for ($i = 0; $i < $numAsatidz; $i++) {
                // Pilih nama yang belum dipakai
                $availableNames = array_diff($namaAsatidz, $usedNames);

                if (empty($availableNames)) {
                    $usedNames = [];
                    $availableNames = $namaAsatidz;
                }

                $nama = $availableNames[array_rand($availableNames)];
                $usedNames[] = $nama;

                $gender = in_array($nama, $femaleNames) ? 'Perempuan' : 'Laki-laki';
                $umur = rand(25, 60);
                $keahlian = $keahlianList[array_rand($keahlianList)];

                $asatidz = Asatidz::create([
                    'profile_masjid_id' => $masjid->id,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(5),
                    'no_handphone' => '081' . rand(100000000, 999999999),
                    'alamat' => $this->generateRandomAddress(),
                    'umur' => $umur,
                    'jenis_kelamin' => $gender,
                    'keahlian' => $keahlian,
                    'keterangan' => "Asatidz spesialis {$keahlian}",
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);

                // Ambil murid TPQ dari masjid ini
                $muridTPQ = Jamaah::where('profile_masjid_id', $masjid->id)
                    ->where('aktivitas_jamaah', 'like', '%TPQ%')
                    ->get();

                // Distribusikan murid TPQ secara merata ke asatidz
                // Setiap asatidz mendapat 2-5 murid TPQ secara acak
                if ($muridTPQ->isNotEmpty()) {
                    $numMurid = rand(2, min(5, $muridTPQ->count()));
                    $selectedMurid = $muridTPQ->random($numMurid);
                    $asatidz->murid()->attach($selectedMurid->pluck('id')->toArray());
                }
            }
        }
    }

    /**
     * Generate random address
     */
    private function generateRandomAddress(): string
    {
        $streets = [
            'KH. Ahmad Dahlan',
            'Sultan Agung',
            'Malioboro',
            'Kaliurang',
            'Gejayan',
            'Urip Sumoharjo',
            'Bantul',
            'Sleman',
            'Kaliurang Km',
            'Palagan'
        ];

        $cities = [
            'Yogyakarta',
            'Bantul',
            'Sleman',
            'Godean',
            'Depok',
            'Berbah'
        ];

        $street = $streets[array_rand($streets)];
        $city = $cities[array_rand($cities)];
        $number = rand(1, 100);

        return "Jl. {$street} No. {$number}, {$city}";
    }
}
