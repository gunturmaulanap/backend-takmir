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

                Takmir::create([
                    'user_id'           => $user->id,
                    'profile_masjid_id' => $masjid->id,
                    'nama'              => 'Takmir ' . $i . ' ' . $masjid->nama,
                    'slug'              => Str::slug($masjid->nama) . '-takmir' . $i,
                    'jabatan'           => 'Takmir ' . $i,
                    'no_handphone'      => '',
                    'umur'              => '',
                    'deskripsi_tugas'   => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'image'             => null,
                ]);
            }
        }
    }
}
