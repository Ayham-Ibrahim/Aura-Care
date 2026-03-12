<?php

namespace Database\Seeders;

use App\Models\Center\Center;
use Illuminate\Database\Seeder;

class CenterWorkingHoursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $centers = Center::all();

        foreach ($centers as $center) {
            if ($center->workingHours()->count() === 0) {
                for ($d = 0; $d < 7; $d++) {
                    $center->workingHours()->create([
                        'day' => $d,
                        'open_time' => '00:00:00',
                        'close_time' => '23:59:00',
                        'is_active' => true,
                    ]);
                }
            }
        }

        $this->command->info('تم ملء أوقات العمل الافتراضية للمراكز، إذا لم تكن موجودة.');
    }
}
