<?php

namespace App\Services;

use App\Models\Section;
use Illuminate\Support\Facades\Log;

class SectionService extends Service
{
    public function getAllSections()
    {
        return Section::select('id', 'name', 'image')->get();
    }

    public function createSection(array $data)
    {
        try {
            $section = Section::create([
                'name' => $data['name'],
                'image' => FileStorage::storeFile($data['image'], 'Section', 'img'),
            ]);
            return $section;
        } catch (\Exception $e) {
            Log::error('Error creating section', ['data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء انشاء القسم');
        }
    }

    public function updateSection(Section $section, array $data)
    {
        try {
            // return $data;
            $section->update([
                'name' => $data['name'] ?? $section->name,
                'image' => FileStorage::fileExists(
                    $data['image'] ?? null,
                    $section->image,
                    'Section',
                    'img'
                ) ?? $section->image,
            ]);


            return $section;
        } catch (\Exception $e) {
            Log::error('Error updating section', ['section_id' => $section->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل القسم');
        }
    }

    public function deleteSection(Section $section): void
    {
        try {
            // delete files for related services first
            foreach ($section->services as $service) {
                FileStorage::deleteFile($service->image);
                $service->delete();
            }

            FileStorage::deleteFile($section->image);
            $section->delete();
        } catch (\Exception $e) {
            Log::error('Error deleting section', ['section_id' => $section->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء حذف القسم');
        }
    }
}
