<?php

namespace App\Services\Center;

use App\Models\Center\Center;
use App\Models\Center\WorkingHour;
use App\Services\Service;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WorkingHourService extends Service
{
    /**
     * Return an array containing hours and default flag
     */
    public function getWorkingHours()
    {
        $center = Auth::guard('center')->user();
        return $center->workingHoursFormatted;
    }

    /**
     * Update the working hours for the authenticated center (or provided one)
     * returns same structure as getWorkingHours plus a flag default_applied
     */
    public function updateWorkingHours(array $data)
    {

        $center = Auth::guard('center')->user();
        $entries = $data['working_hours'] ?? [];
        // $center->workingHours()->delete();
        // return $this->setDefaultForCenter($center); // reset to default before applying updates
        try {

            foreach ($entries as $item) {
                $dayEntry = $center->workingHours()->where('day', $item['day'])->first();
                if ($dayEntry) {
                    $dayEntry->update([
                        'open_time' => $item['open_time'] ?? $dayEntry->open_time,
                        'close_time' => $item['close_time'] ?? $dayEntry->close_time,
                        'is_active' => $item['is_active'] ?? $dayEntry->is_active
                    ]);
                }
            }

            return $this->getWorkingHours();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating working hours', ['center_id' => $center->id, 'data' => $data, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل أوقات العمل');
        }
    }

    /**
     * Create default 24/7 entries for the given center
     */
    public function setDefaultForCenter(Center $center)
    {
        $rows = [];
        for ($d = 0; $d < 7; $d++) {
            $rows[] = [
                'center_id' => $center->id,
                'day' => $d,
                'open_time' => '00:00:00',
                'close_time' => '23:59:00',
                'is_active' => true,
            ];
        }
        $center->workingHours()->insert($rows);
    }

    public function resetWorkingHours()
    {
        try {
            $center = Auth::guard('center')->user();
            $center->workingHours()->delete();
            $this->setDefaultForCenter($center);
            return $this->getWorkingHours();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error resetting working hours', [ 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء إعادة أوقات العمل إلى الافتراضية');
        }
    }
}
