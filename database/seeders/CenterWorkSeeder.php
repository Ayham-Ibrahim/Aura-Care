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
        // حذف البيانات السابقة
        DB::table('work_files')->delete();
        DB::table('works')->delete();

        // الحصول على المراكز والخدمات
        $centers = Center::all();
        $services = Service::all();

        if ($centers->isEmpty() || $services->isEmpty()) {
            $this->command->error('يجب أن تقوم بإنشاء المراكز والخدمات أولاً!');
            return;
        }

        // أعمال شائعة للمراكز التعليمية
        $worksData = [
            // مركز تعليمي - خدمات تعليمية
            [
                'description' => 'تصميم وتنفيذ دورة تدريبية متقدمة في البرمجة بلغة Python',
                'files' => [
                    ['path' => 'works/course_outline.pdf'],
                    ['path' => 'works/training_materials.zip'],
                    ['path' => 'works/certificate_template.jpg'],
                ]
            ],
            [
                'description' => 'تطوير منهج متكامل لتعليم اللغة الإنجليزية للمبتدئين',
                'files' => [
                    ['path' => 'works/english_curriculum.pdf'],
                    ['path' => 'works/audio_lessons.zip'],
                    ['path' => 'works/worksheets.docx'],
                ]
            ],
            [
                'description' => 'تنظيم ورشة عمل في الإسعافات الأولية للمدربين',
                'files' => [
                    ['path' => 'works/first_aid_manual.pdf'],
                    ['path' => 'works/training_videos.mp4'],
                    ['path' => 'works/presentation.pptx'],
                ]
            ],

            // مركز طبي - خدمات صحية
            [
                'description' => 'فحص طبي شامل لـ 50 موظف من شركة التقنية المتقدمة',
                'files' => [
                    ['path' => 'works/medical_report.pdf'],
                    ['path' => 'works/lab_results.xlsx'],
                    ['path' => 'works/recommendations.docx'],
                ]
            ],
            [
                'description' => 'حملة توعوية عن مرض السكري وطرق الوقاية',
                'files' => [
                    ['path' => 'works/awareness_brochure.pdf'],
                    ['path' => 'works/educational_posters.jpg'],
                    ['path' => 'works/lecture_recording.mp4'],
                ]
            ],

            // مركز تقني - خدمات تقنية
            [
                'description' => 'تطوير نظام إدارة للمركز التعليمي يشمل الحضور والغياب',
                'files' => [
                    ['path' => 'works/system_documentation.pdf'],
                    ['path' => 'works/source_code.zip'],
                    ['path' => 'works/user_manual.docx'],
                ]
            ],
            [
                'description' => 'تأسيس بنية تحتية للشبكات والحماية الأمنية',
                'files' => [
                    ['path' => 'works/network_diagram.pdf'],
                    ['path' => 'works/security_protocols.docx'],
                    ['path' => 'works/configuration_files.zip'],
                ]
            ],

            // مركز استشاري
            [
                'description' => 'دراسة جدوى لمشروع مركز تدريب جديد',
                'files' => [
                    ['path' => 'works/feasibility_study.pdf'],
                    ['path' => 'works/financial_analysis.xlsx'],
                    ['path' => 'works/market_research.docx'],
                ]
            ],
        ];

        foreach ($centers as $center) {
            // تحديد الخدمات المتاحة لهذا المركز
            $centerServices = $services->where('center_type_id', $center->center_type_id)->take(3);

            foreach ($centerServices as $service) {
                // اختيار عمل عشوائي
                $workData = $worksData[array_rand($worksData)];
                
                $work = Work::create([
                    'center_id' => $center->id,
                    'service_id' => $service->id,
                    'description' => $workData['description'] . " - " . $center->name,
                ]);

                // إضافة ملفات للعمل
                foreach ($workData['files'] as $fileData) {
                    WorkFile::create([
                        'work_id' => $work->id,
                        'path' => $fileData['path'],
                    ]);
                }
            }

            // إضافة أعمال إضافية لبعض المراكز
            if ($center->id % 2 == 0) { // كل مركز زوجي
                $extraWorkData = $worksData[array_rand($worksData)];
                
                $extraWork = Work::create([
                    'center_id' => $center->id,
                    'service_id' => $centerServices->first()->id,
                    'description' => $extraWorkData['description'] . " (عمل إضافي) - " . $center->name,
                ]);

                foreach ($extraWorkData['files'] as $fileData) {
                    WorkFile::create([
                        'work_id' => $extraWork->id,
                        'path' => 'extra_' . $fileData['path'],
                    ]);
                }
            }
        }

        $this->command->info('تم إنشاء أعمال المراكز بنجاح!');
        $this->command->info('عدد الأعمال: ' . Work::count());
        $this->command->info('عدد الملفات: ' . WorkFile::count());
    }
}