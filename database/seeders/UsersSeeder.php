<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // مسح المستخدمين السابقين

        $users = [
            [
                'name' => 'أحمد محمد',
                'phone' => '01010000001',
                'password' => Hash::make('password123'),
                'is_admin' => 0,
                'gender' => 'male',
                'age' => 30,
                'phone_verified_at' => now(),
                'v_location' => '124',
                'h_location' => '32',
                // 'avatar' => 'avatar1.png',
                // 'sham_image' => 'sham1.png',
                // 'sham_code' => '433c1de779538ec7e82dc81bd8fe2a4f',
            ],
            [
                'name' => 'سارة علي',
                'phone' => '01010000002',
                'password' => Hash::make('password123'),
                'is_admin' => 0,
                'gender' => 'female',
                'age' => 25,
                'phone_verified_at' => now(),
                'v_location' => '125',
                'h_location' => '33',
                // 'avatar' => 'avatar2.png',
                // 'sham_image' => 'sham2.png',
                // 'sham_code' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7',
            ],
            [
                'name' => 'خالد حسن',
                'phone' => '01010000003',
                'password' => Hash::make('password123'),
                'is_admin' => 0,
                'gender' => 'male',
                'age' => 35,
                'phone_verified_at' => now(),
                'v_location' => '126',
                'h_location' => '34',
                // 'avatar' => 'avatar3.png',
                // 'sham_image' => 'sham3.png',
                // 'sham_code' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7',
            ],
            [
                'name' => 'مرحى الفون',
                'phone' => '01010000004',
                'password' => Hash::make('password123'),
                'is_admin' => 0,
                'gender' => 'female',
                'age' => 28,
                'phone_verified_at' => now(),
                'v_location' => '127',
                'h_location' => '35',
                // 'avatar' => 'avatar4.png',
                // 'sham_image' => 'sham4.png',
                // 'sham_code' => 'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7',
            ],
            [
                'name' => 'مستخدم إداري',
                'phone' => '+963910000000',
                'password' => Hash::make('adminpass'),
                'is_admin' => 1,
                'gender' => 'male',
                'age' => 40,
                'phone_verified_at' => now(),
                'v_location' => '128',
                'h_location' => '36',
                // 'avatar' => 'admin_avatar.png',
                // 'sham_image' => 'admin_sham.png',
                // 'sham_code' => 'adminshamcode1234567890abcdef',
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }

        $this->command->info('تم إنشاء ' . User::count() . ' مستخدمين.');
    }
}
