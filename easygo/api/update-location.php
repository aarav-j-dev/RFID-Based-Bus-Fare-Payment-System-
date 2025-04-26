<?php
require_once '../config/db.php';

$rfid_uid = $_POST['rfid_uid'] ?? $_GET['rfid_uid'] ?? null;
$lat = $_POST['lat'] ?? $_GET['lat'] ?? null;
$lng = $_POST['lng'] ?? $_GET['lng'] ?? null;

if (!$rfid_uid || !$lat || !$lng) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$bulk = new MongoDB\Driver\BulkWrite;
$bulk->insert([
    'rfid_uid' => $rfid_uid,
    'lat' => (float)$lat,
    'lng' => (float)$lng,
    'timestamp' => new MongoDB\BSON\UTCDateTime()
]);

$mongo->executeBulkWrite("$database.locations", $bulk);
echo json_encode(["status" => "success", "message" => "Location updated"]);
?>
