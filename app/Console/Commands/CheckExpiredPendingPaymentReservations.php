<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredPendingPaymentReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:check-expired-payments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and cancel reservations that exceeded the 30-minute payment verification period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking expired pending payment reservations...');

        // جلب كل الحجوزات اللي حالتها partially_rejected ومرت 30 دقيقة عليها
        $expiredReservations = Reservation::where('status', 'partially_rejected')
            ->whereNotNull('rejection_time')
            ->where('rejection_time', '<=', now()->subMinutes(30))
            ->get();

        if ($expiredReservations->count() === 0) {
            $this->info('No expired pending payment reservations found.');
            return 0;
        }
        foreach ($expiredReservations as $reservation) {
            try {
                $reservation->update([
                    'status' => 'cancelled',
                    'rejection_time' => null,
                ]);
                $this->info("Reservation #{$reservation->id} cancelled (no payment image).");
                // Log::info('Reservation auto-cancelled after partial rejection timeout', ['reservation_id' => $reservation->id]);
            } catch (\Exception $e) {
                $this->error("Error processing reservation #{$reservation->id}: {$e->getMessage()}");
                Log::error('Error processing expired payment reservation', [
                    'reservation_id' => $reservation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done! Processed {$expiredReservations->count()} expired reservation(s).");
        return 0;
    }
}
