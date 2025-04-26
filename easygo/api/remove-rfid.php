<?php
require_once '../config/db.php';

$email = $_POST['email'] ?? '';

if ($email) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $email],
        ['$unset' => ['rfid_uid' => ""]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<script>alert('RFID has been removed successfully!'); window.location.href='../pages/admin-dashboard.php';</script>";
}
?>
