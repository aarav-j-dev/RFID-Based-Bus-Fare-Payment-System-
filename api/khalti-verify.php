<?php
require_once '../config/db.php';


$data = json_decode(file_get_contents("php://input"));
$token = $data->token ?? '';
$amount = $data->amount ?? 0;
$email = $data->email ?? '';

if (!$email || !$amount) {
    echo json_encode(["success" => false, "message" => "Missing data"]);
    exit;
}


$verified = true;


if ($verified) {
    $points = $amount / 100; 

    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $email],
        ['$inc' => ['balance' => $points]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);


    $query = new MongoDB\Driver\Query(['email' => $email]);
    $user = current($mongo->executeQuery($usersCollection, $query)->toArray());


    $log = new MongoDB\Driver\BulkWrite;
    $log->insert([
        'rfid_uid' => $user->rfid_uid ?? null,
        'email' => $email,
        'amount' => $points,
        'payment_method' => 'Khalti',
        'timestamp' => new MongoDB\BSON\UTCDateTime()
    ]);
    $mongo->executeBulkWrite("$database.topups", $log);

    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "message" => "Verification was failed"]);
}
?>
