<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Calculates great-circle distance between two coordinate pairs using the Haversine formula.
 *
 * @param float $latitudeFrom Latitude of start point in [-90, 90]
 * @param float $longitudeFrom Longitude of start point in [-180, 180]
 * @param float $latitudeTo Latitude of target point in [-90, 90]
 * @param float $longitudeTo Longitude of target point in [-180, 180]
 * @param float $earthRadius Earth radius in meters (default 6371000m)
 * @return float Distance in meters
 */
function calculate_haversine_distance(
    float $latitudeFrom,
    float $longitudeFrom,
    float $latitudeTo,
    float $longitudeTo,
    float $earthRadius = 6371000.0
): float {
    // convert from degrees to radians
    $latFrom = deg2rad($latitudeFrom);
    $lonFrom = deg2rad($longitudeFrom);
    $latTo = deg2rad($latitudeTo);
    $lonTo = deg2rad($longitudeTo);

    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

    return $angle * $earthRadius;
}

/**
 * Validates if coordinates are in valid geographic ranges.
 */
function is_valid_coordinate(?float $lat, ?float $lng): bool
{
    if ($lat === null || $lng === null) {
        return false;
    }
    return ($lat >= -90.0 && $lat <= 90.0) && ($lng >= -180.0 && $lng <= 180.0);
}
