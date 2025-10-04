<?php

namespace Database\Seeders;

use App\Models\Jamaah;
use App\Models\User;
use App\Models\ProfileMasjid;
use App\Models\AktivitasJamaah;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class JamaahTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereIn('id', [2, 3, 4, 5, 6, 7])->get();
        $profileMasjids = ProfileMasjid::whereIn('id', [1, 2, 3, 4, 5, 6])->get();

        // Data 35 nama unik untuk jamaah
        $namaJamaah = [
            'Ahmad Santoso',
            'Budi Hartono',
            'Citra Dewi',
            'Dina Lestari',
            'Eko Prasetyo',
            'Fajar Kurniawan',
            'Gita Cahyani',
            'Hasan Basri',
            'Indra Setiawan',
            'Joko Susilo',
            'Siti Aminah',
            'Rudi Haryanto',
            'Wulan Sari',
            'Bayu Firmansyah',
            'Nina Oktaviani',
            'Andi Saputra',
            'Rina Agustin',
            'Irfan Maulana',
            'Dewi Susanti',
            'Lukman Hakim',
            'Kartika Dwi',
            'Mochamad Rizki',
            'Eka Fitriani',
            'Fadil Pratama',
            'Yulia Indah',
            'Reza Pahlevi',
            'Anisa Rahma',
            'Pramudya Agung',
            'Dyah Ayu',
            'Fikri Haikal',
            'Dendi Wijaya',
            'Ratna Komala',
            'Dimas Prasetya',
            'Shinta Puspita',
            'Yoga Pratama'
        ];

        $aktivitasJamaah = [
            'Sholat Jumat',
            'TPQ',
            'Pengajian Rutin',
            'Kegiatan Sosial',
            'Kegiatan Keagamaan',
            'Relawan Masjid'
        ];

        // Looping untuk setiap user dan masjid
        foreach ($users as $user) {
            $profileMasjid = $profileMasjids->firstWhere('user_id', $user->id);

            // Jika profile masjid tidak ditemukan, lewati perulangan ini
            if (!$profileMasjid) {
                continue;
            }

            // Buat 5 data jamaah untuk user dan masjid ini
            for ($i = 0; $i < 5; $i++) {
                // Ambil nama dari daftar nama unik dan hapus dari array untuk mencegah duplikasi
                $nama = array_shift($namaJamaah);

                $gender = (in_array($nama, ['Citra Dewi', 'Dina Lestari', 'Gita Cahyani', 'Siti Aminah', 'Wulan Sari', 'Nina Oktaviani', 'Rina Agustin', 'Dewi Susanti', 'Kartika Dwi', 'Eka Fitriani', 'Yulia Indah', 'Anisa Rahma', 'Dyah Ayu', 'Ratna Komala', 'Shinta Puspita'])) ? 'Perempuan' : 'Laki-laki';
                $umur = rand(15, 60);

                // Ambil aktivitas jamaah secara acak

                Jamaah::create([
                    'profile_masjid_id' => $profileMasjid->id,
                    'nama' => $nama,
                    'slug' => Str::slug($nama) . '-' . Str::random(5),
                    'no_handphone' => '081' . rand(100000000, 999999999),
                    'alamat' => 'Jl. ' . $nama . ' No. ' . rand(1, 100) . ', Yogyakarta',
                    'umur' => $umur,
                    'jenis_kelamin' => $gender,
                    'aktivitas_jamaah' => $aktivitasJamaah[array_rand($aktivitasJamaah)],
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ]);
            }
        }
    }
}
