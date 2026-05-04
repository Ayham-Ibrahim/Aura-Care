<?php

namespace App\Enums;

enum CenterSortType: string
{
    case CLOSEST = 'closest';
    case HAS_OFFERS = 'has_offers';
    case HIGHEST_RATING = 'highest_rating';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
