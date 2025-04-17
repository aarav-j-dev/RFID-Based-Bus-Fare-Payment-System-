<?php
require_once '../config/db.php';

$query = new MongoDB\Driver\Query(['action' => 'tap_out'], ['sort' => ['timestamp' => -1], 'limit' => 1]);
$last = current($mongo->executeQuery($transactionsCollection, $query)->toArray());

echo json_encode([
    'lat' => $last->lat ?? 27.7083,
    'lng' => $last->lng ?? 85.3253
]);
?>
