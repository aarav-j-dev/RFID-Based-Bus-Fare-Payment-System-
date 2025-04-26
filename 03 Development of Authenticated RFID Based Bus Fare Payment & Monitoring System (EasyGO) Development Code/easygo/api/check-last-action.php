<?php
require_once '../config/db.php';

$rfid = $_POST['rfid_uid'] ?? '';
if (!$rfid) {
    echo "tap_in";
    exit;
}

$query = new MongoDB\Driver\Query(
    ['rfid_uid' => $rfid, 'action' => ['$in' => ['tap_in', 'tap_out']]],
    ['sort' => ['timestamp' => -1], 'limit' => 1]
);

$result = $mongo->executeQuery("$database.transactions", $query)->toArray();

if (count($result) > 0) {
    echo $result[0]->action;
} else {
    echo "tap_in";
}
