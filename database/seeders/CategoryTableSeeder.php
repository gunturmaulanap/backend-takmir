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
            ['name' => 'Kajian Rutin', 'color' => 'Blue'],
            ['name' => 'Pelatihan', 'color' => 'Green'],
            ['name' => 'Sosial', 'color' => 'Purple'],
            ['name' => 'Umum', 'color' => 'Orange'],
            ['name' => 'Keagamaan', 'color' => 'Indigo'],
        ];

        for ($masjidId = 1; $masjidId <= 6; $masjidId++) {
            $selectedCategories = collect($categories)->random(3);
            $userId = $masjidId + 1; // User ID mulai dari 2 hingga 7

            foreach ($selectedCategories as $category) {
                Category::create([
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'color' => $category['color'],
                    'profile_masjid_id' => $masjidId,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }
        }
    }
}
