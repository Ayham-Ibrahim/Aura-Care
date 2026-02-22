<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Center\Center;
use App\Models\ManageSubservice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        // مسح الحجوزات السابقة فقط، المستخدمين يعاد إنشاؤهم في Seeder منفصل
        Reservation::query()->delete();

        // الحصول على البيانات الأساسية
        $centers = Center::all();
        $users = User::all();

        if ($centers->isEmpty() || $users->isEmpty()) {
            $this->command->error('يجب إنشاء المراكز والمستخدمين أولاً!');
            return;
        }

        // حالات الحجز
        $statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        
        // أسباب الإلغاء
        $cancellationReasons = [
            'تغيير في الموعد',
            'ظروف طارئة',
            'وجدت مركز أفضل',
            'تغيير في السعر',
            'مشكلة في الخدمة',
            'غير ذلك',
        ];

        // إنشاء 50 حجز عشوائي
        for ($i = 1; $i <= 50; $i++) {
            $center = $centers->random();
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            
            // إنشاء تاريخ عشوائي
            $date = Carbon::now()
                ->addDays(rand(-30, 60)) // بين 30 يوم ماضي و 60 يوم مستقبل
                ->addHours(rand(8, 20))  // بين 8 صباحاً و 8 مساءً
                ->addMinutes(rand(0, 55)) // دقائق مضاعفات 5
                ->second(0); // ثواني صفر

            $reservationData = [
                'center_id' => $center->id,
                'user_id' => $user->id,
                'total_amount' => rand(50, 5000), // مبلغ بين 50 و 5000
                'status' => $status,
                'date' => $date,
                'deposit_amount' => rand(50, 5000)
            ];

            // إذا كان الحجز ملغى، أضف أسباب الإلغاء
            if ($status === 'cancelled') {
                $reservationData['reason_for_cancellation'] = $cancellationReasons[array_rand($cancellationReasons)];
                $reservationData['cancellation_image'] = 'cancellations/cancel_' . rand(1, 10) . '.jpg';
            }

            // إذا كان الحجز مؤكد أو مكتمل، أضف صورة الدفع
            if (in_array($status, ['confirmed', 'completed'])) {
                $reservationData['payment_image'] = 'payments/payment_' . rand(1, 10) . '.jpg';
            }

            $reservation = Reservation::create($reservationData);

            // إرفاق عناصر manage_subservice ذات علاقة بالمركز
            $manageItems = ManageSubservice::where('center_id', $center->id)->get();
            if ($manageItems->isNotEmpty()) {
                $selected = $manageItems->random(rand(1, min(3, $manageItems->count())))->pluck('id')->toArray();
                $reservation->manageSubservices()->attach($selected);
            }
        }

        // إضافة بعض الأمثلة الواقعية
        $sampleReservations = [
            [
                'center_id' => $centers->first()->id,
                'user_id' => $users->first()->id,
                'total_amount' => 2500,
                'status' => 'confirmed',
                'date' => Carbon::now()->addDays(3)->setTime(14, 30),
                'payment_image' => 'payments/receipt_001.jpg',
                'deposit_amount' => 500,
            ],
            [
                'center_id' => $centers->last()->id,
                'user_id' => $users->last()->id,
                'total_amount' => 1500,
                'status' => 'completed',
                'date' => Carbon::now()->subDays(5)->setTime(10, 0),
                'payment_image' => 'payments/receipt_002.jpg',
                'deposit_amount' => 300,
            ],
            [
                'center_id' => $centers->get(1)->id,
                'user_id' => $users->get(1)->id,
                'total_amount' => 3000,
                'status' => 'cancelled',
                'date' => Carbon::now()->addDays(2)->setTime(16, 45),
                'reason_for_cancellation' => 'تغيير في الموعد',
                'cancellation_image' => 'cancellations/cancel_001.jpg',
                'deposit_amount' => 600,
            ],
        ];

        foreach ($sampleReservations as $reservation) {
            $res = Reservation::create($reservation);

            // attach related manage_subservices if available
            $manageItems = ManageSubservice::where('center_id', $reservation['center_id'])->get();
            if ($manageItems->isNotEmpty()) {
                $res->manageSubservices()->attach($manageItems->random()->pluck('id')->toArray());
            }
        }

        $this->command->info('تم إنشاء ' . Reservation::count() . ' حجز بنجاح!');
        
        // عرض إحصائيات
        $this->command->table(
            ['الحالة', 'العدد'],
            [
                ['pending', Reservation::where('status', 'pending')->count()],
                ['confirmed', Reservation::where('status', 'confirmed')->count()],
                ['cancelled', Reservation::where('status', 'cancelled')->count()],
                ['completed', Reservation::where('status', 'completed')->count()],
            ]
        );
    }
}