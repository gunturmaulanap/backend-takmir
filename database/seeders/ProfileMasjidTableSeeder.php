<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\ProfileMasjid;
use Illuminate\Support\Str;

class ProfileMasjidTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masjids = [
            [
                'name' => 'Masjid Nurul Ashri',
                'alamat' => 'Jalan Deresan III, Gejayan, Sleman',
                'username' => 'suyitno',
            ],
            [
                'name' => 'Masjid Kampus UGM',
                'alamat' => 'Jalan Lingkar Utara, Bulaksumur, Sleman',
                'username' => 'dwiaryo',
            ],
            [
                'name' => 'Masjid Gedhe Kauman',
                'alamat' => 'Jalan Alun-Alun Utara, Kota Yogyakarta',
                'username' => 'dimas',
            ],
            [
                'name' => 'Masjid Jogokariyan',
                'alamat' => 'Jalan Jogokariyan, Mantrijeron, Kota Yogyakarta',
                'username' => 'ponco',
            ],
            [
                'name' => 'Masjid Syuhada',
                'alamat' => 'Jalan I Dewa Nyoman Oka, Kotabaru, Kota Yogyakarta',
                'username' => 'joko',
            ],
            [
                'name' => 'Masjid Al-Falah',
                'alamat' => 'Jalan Cempaka, Condongcatur, Sleman',
                'username' => 'prabu',
            ]
        ];

        foreach ($masjids as $masjid) {
            $user = User::where('username', $masjid['username'])->first();
            if ($user) {
                ProfileMasjid::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nama' => $masjid['name'],
                        'alamat' => $masjid['alamat'],
                        'image' => null,
                        'slug' => Str::slug($masjid['name'])
                    ]
                );
            }
        }
    }
}
