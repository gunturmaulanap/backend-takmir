<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserTableSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin User
        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@gmail.com'],
            [
                'name' => 'Super Admin',
                'username' => 'superadmin',
                'password' => bcrypt('password'),
            ]
        );

        $superAdminRole = Role::where('name', 'superadmin')->where('guard_name', 'api')->first();
        if ($superAdminRole) {
            $allPermissions = Permission::where('guard_name', 'api')->get();
            $superAdminRole->syncPermissions($allPermissions);
            $superAdminUser->assignRole($superAdminRole);
        }

        // Create Admin Users (from user_id 2 to 7)
        $adminRole = Role::where('name', 'admin')->where('guard_name', 'api')->first();
        $adminUsers = [
            'suyitno', // for Masjid Nurul Ashri
            'dwiaryo', // for Masjid Kampus UGM
            'dimas', // for Masjid Gedhe Kauman
            'ponco', // for Masjid Jogokariyan
            'joko', // for Masjid Syuhada
            'prabu', // for Masjid Al-Falah

        ];

        foreach ($adminUsers as $index => $username) {
            $user = User::firstOrCreate(
                ['email' => $username . '@gmail.com'],
                [
                    'name' => ucwords(str_replace('-', ' ', $username)),
                    'username' => $username,
                    'password' => bcrypt('password'),
                ]
            );
            if ($adminRole) {
                $user->assignRole($adminRole);
            }
        }
    }
}
