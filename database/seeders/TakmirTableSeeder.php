<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Takmir;
use App\Models\User;
use App\Models\ProfileMasjid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TakmirTableSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('name', 'takmir')->first();
        if (!$role) {
            throw new \Exception('Role takmir belum ada.');
        }

        $masjids = ProfileMasjid::all();
        $defaultPassword = 'password123';

        // Data random untuk takmir
        $jabatanOptions = ['Ketua Takmir', 'Sekretaris', 'Bendahara', 'Koordinator Umum', 'Koordinator Keamanan'];
        $tugasDescriptions = [
            'Mengelola administrasi dan keuangan masjid',
            'Koordinasi kegiatan rutin dan harian masjid',
            'Mengelola jadwal imam dan muadzin',
            'Menangani perawatan fasilitas masjid',
            'Koordinasi acara keagamaan dan sosial',
            'Pengelolaan inventaris dan aset masjid',
            'Hubungan dengan jamaah dan masyarakat',
            'Pengumpulan dan distribusi dana masjid'
        ];

        foreach ($masjids as $masjid) {
            for ($i = 1; $i <= 5; $i++) {
                $username = Str::slug($masjid->nama) . '_takmir' . $i;
                $user = User::create([
                    'name'     => 'Takmir ' . $i . ' ' . $masjid->nama,
                    'username' => $username,
                    'password' => Hash::make($defaultPassword),
                ]);
                $user->assignRole($role);
                $userId = $masjid->user_id;

                // Generate random phone number
                $phoneNumber = '08' . rand(11, 99) . '-' . rand(1000000, 9999999);

                // Generate random age between 25-65
                $age = rand(25, 65);

                // Get random jabatan and tugas
                $jabatan = $jabatanOptions[$i - 1] ?? 'Anggota Takmir';
                $deskripsiTugas = $tugasDescriptions[array_rand($tugasDescriptions)];

                Takmir::create([
                    'user_id'           => $user->id,
                    'profile_masjid_id' => $masjid->id,
                    'nama'              => 'Takmir ' . $i . ' ' . $masjid->nama,
                    'slug'              => Str::slug($masjid->nama) . '-takmir' . $i,
                    'jabatan'           => $jabatan,
                    'no_handphone'      => $phoneNumber,
                    'umur'              => $age,
                    'deskripsi_tugas'   => $deskripsiTugas,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
