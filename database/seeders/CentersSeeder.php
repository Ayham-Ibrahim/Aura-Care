<?php

namespace Database\Seeders;

use App\Models\Center\Center;
use App\Models\Section;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Center::query()->delete();

        $sections = Section::all();
        if ($sections->isEmpty()) {
            $this->command->error('يجب أن تقوم بتشغيل SectionSeeder أولاً.');
            return;
        }

        $centersData = [
            [
                'section_id' => $sections->random()->id,
                'name' => 'مركز الصحة الأول',
                'logo' => 'centers/center1.jpg',
                'location_h' => 30.044420,
                'location_v' => 31.235712,
                'phone' => '01020000001',
                'phone_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'reliable' => true,
                'owner_name' => 'مالك أول',
                'owner_number' => '01110000001',
                'rating' => 4.5,
            ],
            [
                'section_id' => $sections->random()->id,
                'name' => 'مركز التقنية المتقدمة',
                'logo' => 'centers/center2.png',
                'location_h' => 29.977296,
                'location_v' => 31.132496,
                'phone' => '01020000002',
                'phone_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'reliable' => false,
                'owner_name' => 'مالك ثاني',
                'owner_number' => '01110000002',
                'rating' => 3.8,
            ],
            [
                'section_id' => $sections->random()->id,
                'name' => 'مجمع الخدمات الطبية',
                'logo' => 'centers/center3.jpg',
                'location_h' => 30.013056,
                'location_v' => 31.208853,
                'phone' => '01020000003',
                'phone_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'reliable' => true,
                'owner_name' => 'مالك ثالث',
                'owner_number' => '01110000003',
                'rating' => 4.2,
            ],
        ];

        foreach ($centersData as $data) {
            Center::create($data);
        }

        $this->command->info('تم إنشاء ' . Center::count() . ' مراكز.');
    }
}
