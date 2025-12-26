<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Urutan yang benar:
        $this->call(PermissionsTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(UserTableSeeder::class);

        // Pastikan ProfileMasjidTableSeeder dijalankan sebelum AktivitasJamaahTableSeeder
        $this->call(ProfileMasjidTableSeeder::class);
        $this->call(TakmirTableSeeder::class);
        $this->call(CategoryTableSeeder::class);
        $this->call(ImamTableSeeder::class);
        $this->call(KhatibTableSeeder::class);
        $this->call(MuadzinTableSeeder::class);
        $this->call(JamaahTableSeeder::class);
        $this->call(AsatidzSeeder::class); // Harus setelah JamaahTableSeeder
        $this->call(JadwalKhutbahTableSeeder::class);
        $this->call(EventTableSeeder::class);
        $this->call(TransaksiKeuanganTableSeeder::class);
        $this->call(EventViewTableSeeder::class);
    }
}
