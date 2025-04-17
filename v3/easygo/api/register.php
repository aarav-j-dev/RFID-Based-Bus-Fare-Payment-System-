<?php
require_once '../config/db.php';

$firstname = $_POST['firstname'] ?? '';
$lastname = $_POST['lastname'] ?? '';
$contact = $_POST['contact'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';


if (!$firstname || !$lastname || !$contact || !$email || !$password ) {
    echo "<script>alert('Please fill all fields!'); window.history.back();</script>";
    exit;
}


$query = new MongoDB\Driver\Query([
    '$or' => [
        ['email' => $email],
      
    ]
]);

$existing = $mongo->executeQuery($usersCollection, $query);
$exists = iterator_count($existing);

if ($exists > 0) {
    echo "<script>alert('User with this email already exists!'); window.history.back();</script>";
    exit;
}


$hashedPassword = password_hash($password, PASSWORD_BCRYPT);


$bulk = new MongoDB\Driver\BulkWrite;
$bulk->insert([
    'firstname' => $firstname,
    'lastname' => $lastname,
  
    'contact' => $contact,
    'email' => $email,
   
    'password' => $hashedPassword,
    'role' => 'user',
    'balance' => 0,
    'status' => 'active',
    'created_at' => new MongoDB\BSON\UTCDateTime()
]);

try {
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<script>alert('Registration successful! Please login.'); window.location.href='../pages/login.html';</script>";
} catch (Exception $e) {
    echo "<script>alert('Registration failed. Please try again.'); window.history.back();</script>";
}
?>
