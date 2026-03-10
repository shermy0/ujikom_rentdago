<?php

namespace App\Services;

class TrackingService
{
    /**
     * Calculate distance between two points using Haversine formula
     * 
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in meters
     */
    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth radius in meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($lat1) * cos($lat2) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Check if coordinates are within a specific radius
     * 
     * @param float $cLat Current latitude
     * @param float $cLng Current longitude
     * @param float $dLat Destination latitude
     * @param float $dLng Destination longitude
     * @param int $radius Radius in meters
     * @return bool
     */
    public function isWithinRadius($cLat, $cLng, $dLat, $dLng, $radius = 30)
    {
        $distance = $this->calculateDistance($cLat, $cLng, $dLat, $dLng);
        return $distance <= $radius;
    }
}
