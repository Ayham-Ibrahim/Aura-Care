<?php

namespace Database\Seeders;

use App\Models\Center\Center;
use App\Models\ManageSubservice;
use App\Models\Subservice;
use Illuminate\Database\Seeder;

class ManageSubserviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ManageSubservice::query()->delete();

        $centers = Center::all();
        $subservices = Subservice::all();

        if ($centers->isEmpty() || $subservices->isEmpty()) {
            $this->command->error('يجب إنشاء المراكز والسوبسيرفيسات أولاً.');
            return;
        }

        foreach ($centers as $center) {
            // لكل مركز نضيف بعض السوبسيرفيسات قابلة للإدارة
            $chosen = $subservices->random(min(3, $subservices->count()));
            foreach ($chosen as $sub) {
                ManageSubservice::create([
                    'center_id' => $center->id,
                    'subservice_id' => $sub->id,
                    'price' => rand(100, 1000),
                    'is_active' => (bool) rand(0, 1),
                    'activating_points' => (bool) rand(0, 1),
                    'points' => rand(0, 100),
                    // الحقول من/إلى هي تواريخ
                    'from' => now()->subDays(rand(0, 10))->toDateString(),
                    'to' => now()->addDays(rand(1, 10))->toDateString(),
                ]);
            }
        }

        $this->command->info('تم إنشاء ' . ManageSubservice::count() . ' عناصر manage_subservice.');
    }
}
