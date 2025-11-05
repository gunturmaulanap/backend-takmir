<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            ['nama' => 'Kajian Rutin', 'warna' => 'Blue'],
            ['nama' => 'Pelatihan', 'warna' => 'Green'],
            ['nama' => 'Sosial', 'warna' => 'Purple'],
            ['nama' => 'Umum', 'warna' => 'Orange'],
            ['nama' => 'Keagamaan', 'warna' => 'Indigo'],
        ];

        for ($masjidId = 1; $masjidId <= 6; $masjidId++) {
            $selectedCategories = collect($categories)->random(3);
            $userId = $masjidId + 1; // User ID mulai dari 2 hingga 7

            foreach ($selectedCategories as $category) {
                Category::create([
                    'nama' => $category['nama'],
                    'slug' => Str::slug($category['nama']),
                    'warna' => $category['warna'],
                    'profile_masjid_id' => $masjidId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
