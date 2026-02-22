<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Section;
use Illuminate\Database\Seeder;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::query()->delete();

        $sections = Section::all();
        if ($sections->isEmpty()) {
            $this->command->error('يجب أن تقوم بتشغيل SectionSeeder أولاً.');
            return;
        }

        $servicesData = [];
        // أنشئ بعض الخدمات العامة لكل قسم
        foreach ($sections as $section) {
            $servicesData[] = [
                'name' => 'خدمة عامة - ' . $section->name,
                'image' => 'services/service_' . $section->id . '.jpg',
                'section_id' => $section->id,
            ];
            $servicesData[] = [
                'name' => 'استشارة ' . $section->name,
                'image' => 'services/consult_' . $section->id . '.jpg',
                'section_id' => $section->id,
            ];
        }

        foreach ($servicesData as $data) {
            Service::create($data);
        }

        $this->command->info('تم إنشاء ' . Service::count() . ' خدمات.');
    }
}
