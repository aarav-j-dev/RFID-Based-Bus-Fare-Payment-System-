<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user']) || $_SESSION['role'] !== 'admin') {
    die("Unauthorized");
}

$action = $_POST['action'] ?? '';

if ($action === 'create_user') {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->insert([
        'username' => $_POST['username'],
        'email' => $_POST['email'],
        'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
        'rfid_uid' => $_POST['rfid_uid'],
        'balance' => 0,
        'status' => 'active',
        'role' => 'user'
    ]);
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "User has been created";
}

if ($action === 'toggle_status') {
    $email = $_POST['email'];
    $query = new MongoDB\Driver\Query(['email' => $email]);
    $user = current($mongo->executeQuery($usersCollection, $query)->toArray());

    $newStatus = $user->status === 'blocked' ? 'active' : 'blocked';
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(['email' => $email], ['$set' => ['status' => $newStatus]]);
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "Status has been updated";
}

if ($action === 'add_balance') {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $_POST['email']],
        ['$inc' => ['balance' => (int) $_POST['amount']]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "Balance has been added successfully";
}
?>
