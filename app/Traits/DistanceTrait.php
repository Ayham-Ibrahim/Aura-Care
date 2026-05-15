<?php

namespace App\Traits;

use App\Models\Center\Center;
use App\Models\User;

trait DistanceTrait
{
    public function calculateDistance(User $user, Center $center): ?float
    {
        $latFrom = $user->v_location;
        $lonFrom = $user->h_location;
        $latTo = $center->location_v;
        $lonTo = $center->location_h;

        if ($latFrom === null || $lonFrom === null || $latTo === null || $lonTo === null) {
            return (float) 0; // Return 0 if any of the coordinates are missing
        }

        $earthRadiusKm = 6371;

        $latFromRad = deg2rad($latFrom);
        $lonFromRad = deg2rad($lonFrom);
        $latToRad = deg2rad($latTo);
        $lonToRad = deg2rad($lonTo);

        $latDelta = $latToRad - $latFromRad;
        $lonDelta = $lonToRad - $lonFromRad;

        $a = sin($latDelta / 2) * sin($latDelta / 2)
            + cos($latFromRad) * cos($latToRad)
            * sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadiusKm * $c * 1000, 2);
    }
}
