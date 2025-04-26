<?php
require_once '../config/db.php';

$query = new MongoDB\Driver\Query([], ['sort' => ['timestamp' => -1], 'limit' => 1]);
$cursor = $mongo->executeQuery("$database.locations", $query);
$data = current($cursor->toArray());

if ($data) {
    echo json_encode([
        "lat" => $data->lat,
        "lng" => $data->lng
    ]);
} else {
    echo json_encode(["lat" => 27.7, "lng" => 85.3]); 
}
?>
