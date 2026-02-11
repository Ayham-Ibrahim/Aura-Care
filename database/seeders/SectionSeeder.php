<?php

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = [
            // الأقسام الجراحية
            [
                'name' => 'جراحة القلب والأوعية الدموية',
                'image' => 'cardiac_surgery.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جراحة العظام والمفاصل',
                'image' => 'orthopedics.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جراحة الأعصاب',
                'image' => 'neurosurgery.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'جراحة التجميل',
                'image' => 'plastic_surgery.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // الأقسام الطبية الداخلية
            [
                'name' => 'طب القلب',
                'image' => 'cardiology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب الجهاز الهضمي',
                'image' => 'gastroenterology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب الغدد الصماء',
                'image' => 'endocrinology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب الروماتيزم',
                'image' => 'rheumatology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // الأقسام التخصصية
            [
                'name' => 'طب العيون',
                'image' => 'ophthalmology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب الأنف والأذن والحنجرة',
                'image' => 'ent.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب الجلدية',
                'image' => 'dermatology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'طب النساء والتوليد',
                'image' => 'obgyn.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // أقسام الطوارئ والعناية
            [
                'name' => 'قسم الطوارئ',
                'image' => 'emergency.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'العناية المركزة',
                'image' => 'icu.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'العناية بالأطفال حديثي الولادة',
                'image' => 'neonatal.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // الأقسام التشخيصية
            [
                'name' => 'الأشعة والتصوير الطبي',
                'image' => 'radiology.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'المختبر الطبي',
                'image' => 'laboratory.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'التخدير',
                'image' => 'anesthesia.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // إدراج البيانات في الجدول
        foreach ($sections as $section) {
            Section::create($section);
        }
    }
}
