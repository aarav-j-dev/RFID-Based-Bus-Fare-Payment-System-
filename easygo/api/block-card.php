<?php
require_once '../config/db.php';
$email = $_POST['email'] ?? '';
if (!$email) exit;

$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update(['email' => $email], ['$set' => ['status' => 'blocked']]);
$mongo->executeBulkWrite($usersCollection, $bulk);

header("Location: ../pages/user-dashboard.php");
?>
