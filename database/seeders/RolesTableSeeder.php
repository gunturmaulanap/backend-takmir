<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission; // Tambahkan ini
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan izin sudah ada di database sebelum menjalankan seeder ini.
        // Anda mungkin perlu menjalankan PermissionTableSeeder terlebih dahulu.

        // Membuat role 'superadmin'
        $superadminRole = Role::create([
            'name' => 'superadmin',
            'guard_name' => 'api'
        ]);

        // Membuat role 'admin'
        $adminRole = Role::create([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);

        // Menetapkan permissions untuk role 'admin'
        $adminRole->givePermissionTo([
            "categories.index",
            "categories.create",
            "categories.edit",
            "categories.delete",
            "transaksi-keuangan.index",
            "transaksi-keuangan.create",
            "transaksi-keuangan.edit",
            "transaksi-keuangan.delete",
            "jadwal-khutbah.index",
            "jadwal-khutbah.create",
            "jadwal-khutbah.edit",
            "jadwal-khutbah.delete",
            "takmirs.index",
            "takmirs.create",
            "takmirs.edit",
            "takmirs.delete",
            "muadzins.index",
            "muadzins.create",
            "muadzins.edit",
            "muadzins.delete",
            "imams.index",
            "imams.create",
            "imams.edit",
            "imams.delete",
            "khatibs.index",
            "khatibs.create",
            "khatibs.edit",
            "khatibs.delete",
            "events.index",
            "events.create",
            "events.edit",
            "events.delete",
            "jamaahs.index",
            "jamaahs.create",
            "jamaahs.edit",
            "jamaahs.delete",
            "event_views.index",
            "event_views.create",
            "event_views.delete",
            "event_views.edit",
            "asatidzs.index",
            "asatidzs.create",
            "asatidzs.edit",
            "asatidzs.delete",
            "aktivitas_jamaahs.index",
            "aktivitas_jamaahs.create",
            "aktivitas_jamaahs.edit",
            "aktivitas_jamaahs.delete",
        ]);

        // Membuat role 'takmir'
        $takmirRole = Role::create([
            'name' => 'takmir',
            'guard_name' => 'api'
        ]);

        // Menetapkan permissions untuk role 'takmir'
        $takmirRole->givePermissionTo([
            "categories.index",
            "transaksi-keuangan.index",
            "jadwal-khutbah.index",
            "jadwal-khutbah.create",
            "jadwal-khutbah.edit",
            "jadwal-khutbah.delete",
            "imams.index",
            "imams.create",
            "imams.edit",
            "imams.delete",
            "muadzins.index",
            "muadzins.create",
            "muadzins.edit",
            "muadzins.delete",
            "khatibs.index",
            "khatibs.create",
            "khatibs.edit",
            "khatibs.delete",
            "events.index",
            "jamaahs.index",
            "jamaahs.create",
            "jamaahs.edit",
            "jamaahs.delete",
            "event_views.index"
        ]);
    }
}
