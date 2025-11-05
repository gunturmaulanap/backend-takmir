<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\ProfileMasjid;
use App\Models\Category;
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

            // Ambil 3 kategori berbeda untuk setiap masjid
            $categories = Category::where('profile_masjid_id', $masjid->id)->inRandomOrder()->take(3)->get();

            if ($categories->count() < 1) {
                // Jika tidak ada kategori, skip masjid ini
                continue;
            }

            // Jika kurang dari 3 kategori, gunakan yang tersedia
            $categoryCount = $categories->count();
            $eventCategoryDistribution = [];

            if ($categoryCount >= 3) {
                // Distribusi 4 event ke 3 kategori: 2, 1, 1
                $eventCategoryDistribution = [
                    $categories[0]->id => 2, // 2 event dari kategori pertama
                    $categories[1]->id => 1, // 1 event dari kategori kedua
                    $categories[2]->id => 1, // 1 event dari kategori ketiga
                ];
            } elseif ($categoryCount == 2) {
                // Distribusi 4 event ke 2 kategori: 2, 2
                $eventCategoryDistribution = [
                    $categories[0]->id => 2, // 2 event dari kategori pertama
                    $categories[1]->id => 2, // 2 event dari kategori kedua
                ];
            } else {
                // Jika hanya 1 kategori, semua event dari kategori tersebut
                $eventCategoryDistribution = [
                    $categories[0]->id => 4, // 4 event dari satu-satunya kategori
                ];
            }

            $usedEventNames = []; // Untuk tracking nama event yang sudah dipakai

            // Generate event sesuai distribusi kategori
            foreach ($eventCategoryDistribution as $categoryId => $eventCount) {
                $category = $categories->where('id', $categoryId)->first();
                $categoryName = $category->nama;
                $templateList = $eventTemplates[$categoryName] ?? ['Event Masjid'];

                for ($i = 1; $i <= $eventCount; $i++) {
                    // Pilih nama event yang belum dipakai di masjid ini
                    $availableEventNames = array_diff($templateList, $usedEventNames);

                    // Jika semua nama sudah dipakai, reset array
                    if (empty($availableEventNames)) {
                        $usedEventNames = [];
                        $availableEventNames = $templateList;
                    }

                    $eventName = $availableEventNames[array_rand($availableEventNames)];
                    $usedEventNames[] = $eventName;

                    Event::create([
                        'category_id' => $categoryId,
                        'profile_masjid_id' => $masjid->id,
                        'nama' => $eventName,
                        'slug' => Str::slug($eventName),
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
