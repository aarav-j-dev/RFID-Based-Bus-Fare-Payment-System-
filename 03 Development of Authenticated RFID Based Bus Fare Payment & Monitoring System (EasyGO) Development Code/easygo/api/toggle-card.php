<?php
require_once '../config/db.php';

$email = $_POST['email'] ?? '';
if (!$email) exit;

$query = new MongoDB\Driver\Query(['email' => $email]);
$result = $mongo->executeQuery($usersCollection, $query);
$user = current($result->toArray());

$status = $user->status === 'active' ? 'blocked' : 'active';
$bulk = new MongoDB\Driver\BulkWrite;
$bulk->update(['email' => $email], ['$set' => ['status' => $status]]);
$mongo->executeBulkWrite($usersCollection, $bulk);

header("Location: ../pages/admin-dashboard.php");
?>
