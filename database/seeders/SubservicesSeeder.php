<?php

namespace Database\Seeders;

use App\Models\Subservice;
use App\Models\Service;
use Illuminate\Database\Seeder;

class SubservicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subservice::query()->delete();

        $services = Service::all();
        if ($services->isEmpty()) {
            $this->command->error('يجب أن تقوم بتشغيل ServicesSeeder أولاً.');
            return;
        }

        foreach ($services as $service) {
            // لكل خدمة نضيف عدد قليل من السوبسيرفيسات
            for ($i = 1; $i <= 2; $i++) {
                Subservice::create([
                    'name' => "خدمة فرعية {$i} لـ {$service->name}",
                    'image' => 'subservices/sub_' . $service->id . '_' . $i . '.jpg',
                    'service_id' => $service->id,
                ]);
            }
        }

        $this->command->info('تم إنشاء ' . Subservice::count() . ' سوبسيرفيسات.');
    }
}
