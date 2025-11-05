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

            // Generate 4 event untuk setiap masjid
            for ($i = 1; $i <= 4; $i++) {
                // Ambil kategori random untuk setiap event
                $category = Category::where('profile_masjid_id', $masjid->id)->inRandomOrder()->first();

                if (!$category) {
                    // Jika tidak ada kategori, skip masjid ini
                    continue;
                }

                $categoryName = $category->nama;
                $templateList = $eventTemplates[$categoryName] ?? ['Event Masjid'];
                $eventName = $templateList[array_rand($templateList)];

                // Tambahkan nomor event untuk memastikan nama unik
                $uniqueEventName = $eventName . ' ' . $i;

                $event = Event::create([
                    'category_id' => $category->id,
                    'profile_masjid_id' => $masjid->id,
                    'nama' => $uniqueEventName,
                    'slug' => Str::slug($uniqueEventName),
                    'tanggal_event' => now()->addDays(rand(1, 60)),
                    'waktu_event' => now()->addHours(rand(1, 12))->format('H:i'),
                    'tempat_event' => $this->generateRandomLocation($masjid->nama),
                    'deskripsi' => 'Kegiatan: ' . $eventName . ' ke-' . $i . ' di Masjid ' . $masjid->nama,
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
