<?php
require_once '../config/db.php';

$email = $_POST['email'] ?? '';
$rfid_uid = $_POST['rfid_uid'] ?? '';

if ($email && $rfid_uid) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $email],
        ['$set' => ['rfid_uid' => $rfid_uid]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<script>alert('RFID card ID has been assigned successfully!'); window.location.href='../pages/admin-dashboard.php';</script>";
}
?>
