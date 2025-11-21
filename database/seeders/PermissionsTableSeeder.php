<?php

namespace Database\Seeders;

use Faker\Provider\ar_EG\Person;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        //permission for users
        Permission::create(['name' => 'users.index', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.create', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.edit', 'guard_name' => 'api']);
        Permission::create(['name' => 'users.delete', 'guard_name' => 'api']);

        //permission for permissions
        Permission::create(['name' => 'permissions.index', 'guard_name' => 'api']);

        Permission::create(['name' => 'dashboards.index', 'guard_name' => 'api']);


        Permission::create([
            'name' => 'staffs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'staffs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'staffs.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'staffs.delete',
            'guard_name' => 'api'
        ]);

        //permission for categories
        Permission::create([
            'name' => 'admins.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'admins.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'admins.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'admins.delete',
            'guard_name' => 'api'
        ]);

        //permissions for profile-masjids
        Permission::create([
            'name' => 'profile-masjids.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profile-masjids.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profile-masjids.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profile-masjids.delete',
            'guard_name' => 'api'
        ]);

        //permission for categories
        Permission::create([
            'name' => 'categories.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'categories.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'categories.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'categories.delete',
            'guard_name' => 'api'
        ]);

        //permission for takmir
        Permission::create([
            'name' => 'takmirs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'takmirs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'takmirs.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'takmirs.delete',
            'guard_name' => 'api'
        ]);

        //permission for muadzins
        Permission::create([
            'name' => 'muadzins.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'muadzins.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'muadzins.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'muadzins.delete',
            'guard_name' => 'api'
        ]);
        //permission for imam
        Permission::create([
            'name' => 'imams.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'imams.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'imams.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'imams.delete',
            'guard_name' => 'api'
        ]);

        //permission for profil masjid
        Permission::create([
            'name' => 'profilemasjids.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profilemasjids.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profilemasjids.delete',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'profilemasjids.create',
            'guard_name' => 'api'
        ]);


        //permission for khatibs
        Permission::create([
            'name' => 'khatibs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'khatibs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'khatibs.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'khatibs.delete',
            'guard_name' => 'api'
        ]);

        //permission for roles
        Permission::create([
            'name' => 'roles.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'roles.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'roles.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'roles.delete',
            'guard_name' => 'api'
        ]);

        //permission for events
        Permission::create([
            'name' => 'events.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'events.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'events.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'events.delete',
            'guard_name' => 'api'
        ]);

        //permission for eventView
        Permission::create([
            'name' => 'event_views.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'event_views.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'event_views.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'event_views.delete',
            'guard_name' => 'api'
        ]);

        //permission for moduls
        Permission::create([
            'name' => 'moduls.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'moduls.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'moduls.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'moduls.delete',
            'guard_name' => 'api'
        ]);

        //permission for jamaah
        Permission::create([
            'name' => 'jamaahs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'jamaahs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'jamaahs.edit',
            'guard_name' => 'api'
        ]);

        Permission::create([
            'name' => 'jamaahs.delete',
            'guard_name' => 'api'
        ]);

        //permission for aktivitas jamaah
        Permission::create([
            'name' => 'aktivitas_jamaahs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'aktivitas_jamaahs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'aktivitas_jamaahs.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'aktivitas_jamaahs.delete',
            'guard_name' => 'api'
        ]);

        //permission for asatidz
        Permission::create([
            'name' => 'asatidzs.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'asatidzs.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'asatidzs.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'asatidzs.delete',
            'guard_name' => 'api'
        ]);
        //permission for transaksi keuangan
        Permission::create([
            'name' => 'transaksi-keuangan.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'transaksi-keuangan.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'transaksi-keuangan.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'transaksi-keuangan.delete',
            'guard_name' => 'api'
        ]);
        //permission for jadwal petugas
        Permission::create([
            'name' => 'jadwal-petugas.index',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'jadwal-petugas.create',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'jadwal-petugas.edit',
            'guard_name' => 'api'
        ]);
        Permission::create([
            'name' => 'jadwal-petugas.delete',
            'guard_name' => 'api'
        ]);
    }
}
