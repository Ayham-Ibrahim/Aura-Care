<?php

namespace Database\Seeders;

use App\Models\Center\Center;
use App\Models\Center\Work;
use App\Models\Center\WorkFile;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CenterWorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تنظيف الجداول القديمة
        DB::table('work_files')->delete();
        DB::table('works')->delete();

        $centers = Center::all();
        $services = Service::all();

        if ($centers->isEmpty() || $services->isEmpty()) {
            $this->command->error('يجب أن تقوم بإنشاء المراكز والخدمات أولاً!');
            return;
        }

        // أوصاف بسيطة للأعمال
        $descriptions = [
            'تقرير حالة',
            'دورة تدريبية',
            'تقييم خدمة',
            'بحث ميداني',
        ];

        foreach ($centers as $center) {
            // نختار الخدمات المطابقة لقسم المركز
            $available = $services->where('section_id', $center->section_id);

            if ($available->isEmpty()) {
                $this->command->warn("لا توجد خدمات لقسم المركز {$center->name}، تخطي.");
                continue;
            }

            $selected = $available->random(min(2, $available->count()));

            foreach ($selected as $service) {
                $work = Work::create([
                    'center_id' => $center->id,
                    'service_id' => $service->id,
                    'description' => $descriptions[array_rand($descriptions)] . ' - ' . $center->name,
                ]);

                // إضافة ملفين افتراضيين
                WorkFile::create(['work_id' => $work->id, 'path' => 'works/sample1.pdf']);
                WorkFile::create(['work_id' => $work->id, 'path' => 'works/sample2.pdf']);
            }
        }

        $this->command->info('تم إنشاء أعمال المراكز بنجاح!');
        $this->command->info('عدد الأعمال: ' . Work::count());
        $this->command->info('عدد الملفات: ' . WorkFile::count());
    }
}