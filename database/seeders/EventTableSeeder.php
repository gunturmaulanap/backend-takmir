<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\ProfileMasjid;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Str;

class EventTableSeeder extends Seeder
{
    public function run(): void
    {
        $masjids = ProfileMasjid::all();
        $eventTemplates = [
            'Pelatihan' => [
                'Pelatihan Manajemen Masjid',
                'Pelatihan Dakwah Digital',
                'Pelatihan Kepemimpinan Remaja Masjid',
            ],
            'Sosial' => [
                'Bakti Sosial Ramadhan',
                'Santunan Anak Yatim',
                'Donor Darah Bersama',
            ],
            'Keagamaan' => [
                'Peringatan Maulid Nabi',
                'Isra Miraj & Doa Bersama',
                'Pesantren Kilat Ramadhan',
            ],
            'Kajian Rutin' => [
                'Kajian Tafsir Al-Qur\'an',
                'Kajian Fiqih Keluarga',
                'Kajian Hadits Arbain',
            ],
            'Umum' => [
                'Lomba Cerdas Cermat Islam',
                'Bazar Ramadhan',
                'Family Gathering Jamaah',
            ],
        ];

        foreach ($masjids as $masjid) {
            $userId = $masjid->user_id;
            // Ambil 3 kategori random untuk event
            $categories = Category::where('profile_masjid_id', $masjid->id)->inRandomOrder()->take(3)->get();
            foreach ($categories as $category) {
                $categoryName = $category->name;
                $templateList = $eventTemplates[$categoryName] ?? ['Event Masjid'];
                $eventName = $templateList[array_rand($templateList)];
                $event = Event::create([
                    'category_id' => $category->id,
                    'profile_masjid_id' => $masjid->id,
                    'nama' => $eventName,
                    'slug' => Str::slug($eventName) . '-' . Str::random(3),
                    'tanggal_event' => now()->addDays(rand(1, 60)),
                    'waktu_event' => now()->addHours(rand(1, 12))->format('H:i'),
                    'tempat_event' => $this->generateRandomLocation($masjid->nama),
                    'deskripsi' => 'Kegiatan: ' . $eventName . ' di Masjid ' . $masjid->nama,
                    'image' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }

    private function generateRandomLocation($masjidNama): string
    {
        $locations = [
            'Aula ' . $masjidNama,
            'Halaman ' . $masjidNama,
            'Ruang Serbaguna ' . $masjidNama,
            'Teras ' . $masjidNama,
            'Mushola ' . $masjidNama,
            'Ruang Pertemuan ' . $masjidNama,
            'Lapangan ' . $masjidNama,
            'Gedung ' . $masjidNama,
        ];

        return $locations[array_rand($locations)];
    }
}
