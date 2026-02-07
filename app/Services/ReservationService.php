<?php

namespace App\Services;

use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class ReservationService extends Service
{
    public function getAllReservations()
    {
        return Reservation::with('center:id,name','user:id,name')->get();
    }

    // public function createReservation(array $data)
    // {
    //     try {
    //         $reservation = Reservation::create([
    //             'center_id' => $data['center_id'],
    //             'user_id' => $data['user_id'],
    //             'total_amount' => $data['total_amount'] ?? null,
    //             'status' => $data['status'] ?? 'pending',
    //             'date' => $data['date'] ?? null,
    //             'hour' => $data['hour'] ?? null,
    //             'payment_image' => isset($data['payment_image']) ? FileStorage::storeFile($data['payment_image'], 'Reservation', 'img') : null,
    //             'cancellation_image' => isset($data['cancellation_image']) ? FileStorage::storeFile($data['cancellation_image'], 'Reservation', 'img') : null,
    //             'reason_for_cancellation' => $data['reason_for_cancellation'] ?? null,
    //         ]);

    //         return $reservation;
    //     } catch (\Exception $e) {
    //         Log::error('Error creating reservation', ['data' => $data, 'error' => $e->getMessage()]);
    //         $this->throwExceptionJson('حدث خطأ ما أثناء إنشاء الحجز');
    //     }
    // }

    // public function updateReservation(Reservation $reservation, array $data)
    // {
    //     try {
    //         $reservation->update([
    //             'center_id' => $data['center_id'] ?? $reservation->center_id,
    //             'user_id' => $data['user_id'] ?? $reservation->user_id,
    //             'total_amount' => $data['total_amount'] ?? $reservation->total_amount,
    //             'status' => $data['status'] ?? $reservation->status,
    //             'date' => $data['date'] ?? $reservation->date,
    //             'hour' => $data['hour'] ?? $reservation->hour,
    //             'payment_image' => FileStorage::fileExists($data['payment_image'] ?? null, $reservation->payment_image, 'Reservation', 'img') ?? $reservation->payment_image,
    //             'cancellation_image' => FileStorage::fileExists($data['cancellation_image'] ?? null, $reservation->cancellation_image, 'Reservation', 'img') ?? $reservation->cancellation_image,
    //             'reason_for_cancellation' => $data['reason_for_cancellation'] ?? $reservation->reason_for_cancellation,
    //         ]);

    //         return $reservation;
    //     } catch (\Exception $e) {
    //         Log::error('Error updating reservation', ['reservation_id' => $reservation->id, 'data' => $data, 'error' => $e->getMessage()]);
    //         $this->throwExceptionJson('حدث خطأ ما أثناء تعديل الحجز');
    //     }
    // }

    public function updateReservationStatus(Reservation $reservation, string $status): Reservation
    {
        try {
            $reservation->status = $status;
            $reservation->save();
            return $reservation;
        } catch (\Exception $e) {
            Log::error('Error updating reservation status', ['reservation_id' => $reservation->id, 'status' => $status, 'error' => $e->getMessage()]);
            $this->throwExceptionJson('حدث خطأ ما أثناء تعديل حالة الحجز');
        }
    }

    // public function deleteReservation(Reservation $reservation): void
    // {
    //     try {
    //         FileStorage::deleteFile($reservation->payment_image);
    //         FileStorage::deleteFile($reservation->cancellation_image);
    //         $reservation->delete();
    //     } catch (\Exception $e) {
    //         Log::error('Error deleting reservation', ['reservation_id' => $reservation->id, 'error' => $e->getMessage()]);
    //         $this->throwExceptionJson('حدث خطأ ما أثناء حذف الحجز');
    //     }
    // }
}
