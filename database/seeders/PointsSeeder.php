<?php

namespace Database\Seeders;

use App\Models\Center\Center;
use App\Models\Point;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Point::query()->delete();

        $users = User::where('is_admin', false)->get();
        $centers = Center::all();

        if ($users->isEmpty()) {
            $this->command->error('لا توجد مستخدمين غير إداريين لعمل نقاط لهم.');
            return;
        }

        if ($centers->isEmpty()) {
            $this->command->error('لا توجد مراكز لعمل نقاط المستخدمين عندها.');
            return;
        }

        foreach ($users as $user) {
            foreach ($centers as $center) {
                Point::create([
                    'user_id' => $user->id,
                    'center_id' => $center->id,
                    'points' => ($user->id + $center->id) * 10,
                ]);
            }
        }

        $this->command->info('تم إنشاء نقاط لكل مستخدم عند كل مركز.');
    }
}
