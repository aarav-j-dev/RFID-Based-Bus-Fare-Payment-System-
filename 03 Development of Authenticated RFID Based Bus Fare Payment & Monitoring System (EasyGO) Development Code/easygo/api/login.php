<?php
session_start();
require_once '../config/db.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

$query = new MongoDB\Driver\Query(['email' => $email]);
$result = $mongo->executeQuery($usersCollection, $query);
$user = current($result->toArray());

if (!$user || !password_verify($password, $user->password)) {
    echo "<script>alert('Invalid credentials were entered'); window.history.back();</script>";
    exit;
}

$_SESSION['user'] = $user->email;
$_SESSION['role'] = $user->role ?? 'user';

if ($_SESSION['role'] == 'admin') {
    header("Location: ../pages/admin-dashboard.php");
} else {
    header("Location: ../pages/user-dashboard.php");
}
?>
