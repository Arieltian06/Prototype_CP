<?php

namespace App\Services;

use DomainException;

class DistanceService
{
    /** Calculate great-circle distance in km; this is not road or travel distance. */
    public function calculateDistance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        foreach ([[$lat1, $lng1], [$lat2, $lng2]] as [$latitude, $longitude]) {
            if (! is_finite($latitude) || ! is_finite($longitude)
                || $latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
                throw new DomainException('Koordinat tidak valid. Latitude harus -90 hingga 90 dan longitude -180 hingga 180.');
            }
        }

        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        // Floating-point drift near antipodes can otherwise put sqrt/asin outside their domain.
        return 6371.0088 * 2 * asin(sqrt(min(1.0, max(0.0, $a))));
    }
}
