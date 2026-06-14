<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Reservation;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        Comment::query()->delete();

        $reservations = Reservation::whereIn('status', [ 'completed'])->get();
        if ($reservations->isEmpty()) {
            $this->command->info('لا توجد حجوزات مكتملة لإنشاء تعليقات تجريبية.');
            return;
        }

        $sampleTexts = [
            'الخدمة كانت ممتازة وسريعة جداً.',
            'الموظفين كانوا ودودين والمحطة نظيفة.',
            'التجربة جيدة، لكن يمكن تحسين وقت الانتظار.',
            'سأعود مرة أخرى، شكراً لكم.',
            'التعامل احترافي والأسعار مناسبة.',
        ];

        $reservations->random(min(10, $reservations->count()))->each(function ($reservation) use ($sampleTexts) {
            Comment::create([
                'user_id' => $reservation->user_id,
                'center_id' => $reservation->center_id,
                'reservation_id' => $reservation->id,
                'text' => $sampleTexts[array_rand($sampleTexts)],
                'is_edited' => false,
            ]);
        });

        $this->command->info('تم إنشاء تعليقات تجريبية بنجاح.');
    }
}
