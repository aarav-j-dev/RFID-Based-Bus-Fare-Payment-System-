<?php
require_once '../config/db.php';

$email = $_POST['email'] ?? '';
$amount = (float)($_POST['amount'] ?? 0);

if ($email && $amount > 0) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $email],
        ['$inc' => ['balance' => -$amount]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<script>alert('Balance has been deducted!'); window.location.href='../pages/admin-dashboard.php';</script>";
}
?>
