<?php

namespace App\Services\Center;

use App\Models\Center\Work;
use App\Services\FileStorage;
use App\Services\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkService extends Service
{
    public function WorksByService($Service)
    {
        try {
            $center = Auth::guard('center')->user();
            $works = Work::with('files')
                ->where('center_id', $center->id)
                ->where('service_id', $Service->id)
                ->get();
            return $works;
        } catch (\Exception $e) {
            Log::error('Error getting works for service', ['center_id' => $center->id, 'service_id' => $Service->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء جلب الأعمال الخاصة بالخدمة');
        }
    }

    public function createWork($service, $data)
    {
        try {
            $center = Auth::guard('center')->user();

            DB::beginTransaction();
            $work = Work::create([
                'center_id' => $center->id,
                'service_id' => $service->id,
                'description' => $data['description'],
                'video_path' => $data['video_path'] ?? null,
            ]);

            if (isset($data['images'])) {
                foreach ($data['images'] as $file) {
                    $work->files()->create([
                        'path' => FileStorage::storeFile($file, 'Work', 'img'),
                    ]);
                }
            }
            DB::commit();

            return $work;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating work', ['center_id' => $center->id, 'service_id' => $service->id, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطاء ما اثناء إنشاء عمل المركز');
        }
    }
}
