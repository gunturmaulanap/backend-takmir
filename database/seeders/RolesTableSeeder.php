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

        // Membuat atau mencari role 'superadmin'
        $superadminRole = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'api'
        ]);

        // Hapus semua permission lama dan tetapkan yang baru
        $superadminRole->syncPermissions([
            "admins.index",
            "admins.create",
            "admins.edit",
            "admins.delete",
            "profile-masjids.index",
            "profile-masjids.create",
            "profile-masjids.edit",
            "profile-masjids.delete",
            "dashboards.index",

        ]);

        // Membuat atau mencari role 'admin'
        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'api'
        ]);

        // Hapus semua permission lama dan tetapkan yang baru
        $adminRole->syncPermissions([
            "staffs.index",
            "staffs.create",
            "staffs.edit",
            "staffs.delete",
            "dashboards.index",
            "categories.index",
            "categories.create",
            "categories.edit",
            "categories.delete",
            "transaksi-keuangan.index",
            "transaksi-keuangan.create",
            "transaksi-keuangan.edit",
            "transaksi-keuangan.delete",
            "jadwal-petugas.index",
            "jadwal-petugas.create",
            "jadwal-petugas.edit",
            "jadwal-petugas.delete",
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
            // "transaksi-keuangan.index",
            // "transaksi-keuangan.create",
            // "transaksi-keuangan.edit",
            // "transaksi-keuangan.delete",
        ]);

        // Membuat atau mencari role 'takmir'
        $takmirRole = Role::firstOrCreate([
            'name' => 'takmir',
            'guard_name' => 'api'
        ]);

        // Hapus semua permission lama dan tetapkan yang baru
        $takmirRole->syncPermissions([
            "staffs.index",
            "staffs.create",
            "staffs.edit",
            "staffs.delete",
            "dashboards.index",
            "categories.index",
            "transaksi-keuangan.index",
            "jadwal-petugas.index",
            "jadwal-petugas.create",
            "jadwal-petugas.edit",
            "jadwal-petugas.delete",
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
            "event_views.index",
            "asatidzs.index",
            "asatidzs.create",
            "asatidzs.edit",
            "asatidzs.delete",
            // "transaksi-keuangan.index",
            // "transaksi-keuangan.create",
            // "transaksi-keuangan.edit",
            // "transaksi-keuangan.delete",
        ]);
    }
}
