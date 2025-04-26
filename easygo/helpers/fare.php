<?php
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2)**2;
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

function calculateFare($startLat, $startLng, $endLat, $endLng) {
    $distance = calculateDistance($startLat, $startLng, $endLat, $endLng);
    $baseFare = 5;
    $perKm = 2;
    return ceil($baseFare + ($distance * $perKm));
}
?>
