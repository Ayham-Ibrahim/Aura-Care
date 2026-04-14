<?php

namespace App\Console\Commands;

use App\Models\Offer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeleteExpiredOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'offers:delete-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete offers that have expired (end date is in the past)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired offers...');

        // Get all offers where the 'to' date is in the past
        $expiredOffers = Offer::where('to', '<', now())->get();

        if ($expiredOffers->count() === 0) {
            $this->info('No expired offers found.');
            return 0;
        }

        $this->info("Found {$expiredOffers->count()} expired offer(s). Deleting...");

        foreach ($expiredOffers as $offer) {
            try {
                // Log::info('Deleting expired offer', [
                //     'offer_id' => $offer->id,
                //     'center_id' => $offer->center_id,
                //     'end_date' => $offer->to,
                // ]);

                $offer->delete();
                $this->info("Deleted offer #{$offer->id} (expired on: {$offer->to})");
            } catch (\Exception $e) {
                $this->error("Error deleting offer #{$offer->id}: {$e->getMessage()}");
                Log::error('Error deleting expired offer', [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Done! Deleted {$expiredOffers->count()} expired offer(s).");
        return 0;
    }
}
