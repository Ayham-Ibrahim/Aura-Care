<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\User;
use App\Models\Center\Center;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {

        User::query()->delete();
        $users = [
            [
                'name' => 'أحمد محمد',
                'email' => 'ahmed@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'سارة علي',
                'email' => 'sara@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'خالد حسن',
                'email' => 'khaled@example.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $user) {
            User::create($user);
        }
        // حذف الحجوزات السابقة
        Reservation::query()->delete();

        // الحصول على البيانات
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

            Reservation::create($reservationData);
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
            ],
            [
                'center_id' => $centers->last()->id,
                'user_id' => $users->last()->id,
                'total_amount' => 1500,
                'status' => 'completed',
                'date' => Carbon::now()->subDays(5)->setTime(10, 0),
                'payment_image' => 'payments/receipt_002.jpg',
            ],
            [
                'center_id' => $centers->get(1)->id,
                'user_id' => $users->get(1)->id,
                'total_amount' => 3000,
                'status' => 'cancelled',
                'date' => Carbon::now()->addDays(2)->setTime(16, 45),
                'reason_for_cancellation' => 'تغيير في الموعد',
                'cancellation_image' => 'cancellations/cancel_001.jpg',
            ],
        ];

        foreach ($sampleReservations as $reservation) {
            Reservation::create($reservation);
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