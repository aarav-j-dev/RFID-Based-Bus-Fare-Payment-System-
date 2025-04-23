<?php
header("Content-Type: application/json");
require_once '../config/db.php';
require_once '../helpers/fare.php';

$rfid_uid = $_POST['rfid_uid'] ?? '';
$action = $_POST['action'] ?? '';
$lat = $_POST['lat'] ?? null;
$lng = $_POST['lng'] ?? null;

$query = new MongoDB\Driver\Query(['rfid_uid' => $rfid_uid, 'status' => 'active']);

$result = $mongo->executeQuery($usersCollection, $query);
$user = current($result->toArray());

if (!$user) {
    echo json_encode(["NO CARD"]);
    exit;
}

if ($action === 'tap_in') {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'rfid_uid' => $rfid_uid,
        'lat' => (float)$lat,
        'lng' => (float)$lng,
        'action' => 'tap_in',
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);
    $mongo->executeBulkWrite($transactionsCollection, $bulk);
    echo json_encode(["Tap recorded"]);
    exit;
}

if ($action === 'tap_out') {
    $query = new MongoDB\Driver\Query(['rfid_uid' => $rfid_uid, 'action' => 'tap_in'], ['sort' => ['timestamp' => -1], 'limit' => 1]);
    $result = $mongo->executeQuery($transactionsCollection, $query);
    $lastTap = current($result->toArray());

    if (!$lastTap) {
        echo json_encode(["No previous tap"]);
        exit;
    }

    $fare = calculateFare($lastTap->lat, $lastTap->lng, (float)$lat, (float)$lng);
    $balance = $user->balance ?? 0;

    if ($balance < $fare) {
        echo json_encode(["Insufficient balance"]);
        exit;
    }

    $update = new MongoDB\Driver\BulkWrite;
    $update->update(['_id' => $user->_id], ['$inc' => ['balance' => -$fare]]);
    $mongo->executeBulkWrite($usersCollection, $update);

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'rfid_uid' => $rfid_uid,
        'lat' => (float)$lat,
        'lng' => (float)$lng,
        'action' => 'tap_out',
        'fare' => $fare,
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);
    $mongo->executeBulkWrite($transactionsCollection, $bulk);
    echo json_encode(["fare" => $fare]);
}
?>
