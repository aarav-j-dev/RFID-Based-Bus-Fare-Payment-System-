<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user'])) {
    die("Unauthorized");
}

$action = $_POST['action'] ?? '';

if ($action === 'topup') {
    $amount = (int) $_POST['amount'];
    if ($amount > 0) {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(['username' => $_SESSION['user']], ['$inc' => ['balance' => $amount]]);
        $mongo->executeBulkWrite($usersCollection, $bulk);
        echo "Top-up was successful";
    }
}

if ($action === 'block_card') {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(['username' => $_SESSION['user']], ['$set' => ['status' => 'blocked']]);
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "Card has been blocked";
}
?>
