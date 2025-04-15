<?php include('../api/session-check.php'); ?>
<?php if ($_SESSION['role'] !== 'admin') { header("Location: user-dashboard.php"); exit; } ?>
<?php require_once '../config/db.php'; ?>

<!DOCTYPE html>
<html>
<head>
  <title>MAIN DASHBOARD</title>
</head>
<body>
  <h2>Admin Panel</h2>

  
  <form method="POST" action="">
    <h3>Add or Deduct Balance</h3>
    User Email: <input type="email" name="email" required>
    Amount (+  / - ): <input type="number" name="amount" required>
    <input type="submit" name="update_balance" value="Submit">
  </form>

  <!-- Assign/Remove RFID -->
  <form method="POST" action="">
    <h3>Assign or Remove RFID Card</h3>
    User Email: <input type="email" name="email_rfid" required>
    RFID UID: <input type="text" name="rfid_uid">
    <input type="submit" name="assign_rfid" value="Assign/Update RFID">
    <input type="submit" name="remove_rfid" value="Remove RFID">
  </form>

<?php

if (isset($_POST['update_balance'])) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $_POST['email']],
        ['$inc' => ['balance' => (int)$_POST['amount']]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<p>Balance updated.</p>";
}


if (isset($_POST['assign_rfid']) && $_POST['rfid_uid'] !== '') {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $_POST['email_rfid']],
        ['$set' => ['rfid_uid' => $_POST['rfid_uid']]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<p>RFID assigned.</p>";
}


if (isset($_POST['remove_rfid'])) {
    $bulk = new MongoDB\Driver\BulkWrite;
    $bulk->update(
        ['email' => $_POST['email_rfid']],
        ['$unset' => ['rfid_uid' => ""]]
    );
    $mongo->executeBulkWrite($usersCollection, $bulk);
    echo "<p>RFID removed.</p>";
}
?>

  <!-- Search Users -->
  <h3>Search User</h3>
  <form method="GET" action="">
    <input type="text" name="search_email" placeholder="Enter email to search">
    <input type="submit" value="Search">
  </form>

  <h3>All Users</h3>
  <ul>
<?php
$filter = [];
if (!empty($_GET['search_email'])) {
    $filter = ['email' => $_GET['search_email']];
}

$query = new MongoDB\Driver\Query($filter);
$users = $mongo->executeQuery($usersCollection, $query);

foreach ($users as $u) {
    echo "<li>
      <strong>Name:</strong> {$u->firstname} {$u->lastname}<br>
      <strong>Email:</strong> {$u->email}<br>
      <strong>Contact:</strong> {$u->contact}<br>
      <strong>RFID:</strong> " . ($u->rfid_uid ?? 'Not Assigned') . "<br>
      <strong>Status:</strong> {$u->status}<br>
      <strong>Balance:</strong> ₹{$u->balance}<br>
      <form style='display:inline' method='POST' action='../api/toggle-card.php'>
        <input type='hidden' name='email' value='{$u->email}'>
        <input type='submit' value='" . ($u->status == 'active' ? 'Block' : 'Unblock') . "'>
      </form>";

   
    if (isset($u->rfid_uid)) {
        $travelQuery = new MongoDB\Driver\Query(
            ['rfid_uid' => $u->rfid_uid, 'action' => 'tap_out'],
            ['sort' => ['timestamp' => -1], 'limit' => 5]
        );
        $records = $mongo->executeQuery("$database.transactions", $travelQuery);
        echo "<br><strong>Recent Travel History:</strong><ul>";
        foreach ($records as $r) {
            $time = $r->timestamp->toDateTime()->format("Y-m-d H:i:s");
            echo "<li> $time | Fare: ₹{$r->fare} | Lat: {$r->lat}, Lng: {$r->lng}</li>";
        }
        echo "</ul>";
    }

    echo "<hr></li>";
}
?>
  </ul>
  <h2>Transaction Summary</h2>
<form method="GET" style="margin-bottom: 20px;">
  From: <input type="date" name="from" required>
  To: <input type="date" name="to" required>
  <input type="submit" value="Apply Filter">
</form>

<div style="display: flex; flex-wrap: wrap; gap: 20px;">
  <div><canvas id="fareChart" width="250" height="250"></canvas></div>
  <div><canvas id="tripChart" width="250" height="250"></canvas></div>
  <div><canvas id="topupChart" width="250" height="250"></canvas></div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<?php
if (isset($_GET['from']) && isset($_GET['to'])) {
    $from = strtotime($_GET['from']);
    $to = strtotime($_GET['to']) + 86400;

    $filter = ['timestamp' => [
        '$gte' => new MongoDB\BSON\UTCDateTime($from * 1000),
        '$lte' => new MongoDB\BSON\UTCDateTime($to * 1000)
    ]];
    
    $fareQuery = new MongoDB\Driver\Query(array_merge($filter, ['action' => 'tap_out']));
    $fareResult = $mongo->executeQuery("$database.transactions", $fareQuery);
    
    $fareData = [];
    $fareLabels = [];

    foreach ($fareResult as $txn) {
        $userEmail = $txn->email ?? $txn->rfid_uid ?? 'Unknown';
        $fareData[$userEmail] = ($fareData[$userEmail] ?? 0) + ($txn->fare ?? 0);
    }

    
    $tripData = [];
    $tripQuery = new MongoDB\Driver\Query($filter);
    $tripResult = $mongo->executeQuery("$database.transactions", $tripQuery);

    foreach ($tripResult as $trip) {
        $userEmail = $trip->email ?? $trip->rfid_uid ?? 'Unknown';
        $tripData[$userEmail] = ($tripData[$userEmail] ?? 0) + 1;
    }

 
    $topupQuery = new MongoDB\Driver\Query($filter);
    $topupResult = $mongo->executeQuery("$database.topups", $topupQuery);

    $topupData = [];
    foreach ($topupResult as $topup) {
        $userEmail = $topup->email ?? 'Unknown';
        $topupData[$userEmail] = ($topupData[$userEmail] ?? 0) + ($topup->amount ?? 0);
    }

 
    echo "<script>
        const fareChart = new Chart(document.getElementById('fareChart'), {
            type: 'pie',
            data: {
                labels: " . json_encode(array_keys($fareData)) . ",
                datasets: [{
                    label: 'Fare Collection',
                    data: " . json_encode(array_values($fareData)) . ",
                    backgroundColor: ['#3366cc', '#dc3912', '#ff9900', '#109618', '#990099']
                }]
            }
        });

        const tripChart = new Chart(document.getElementById('tripChart'), {
            type: 'pie',
            data: {
                labels: " . json_encode(array_keys($tripData)) . ",
                datasets: [{
                    label: 'Total Trips',
                    data: " . json_encode(array_values($tripData)) . ",
                    backgroundColor: ['#ff6384', '#36a2eb', '#ffce56', '#8e5ea2', '#3cba9f']
                }]
            }
        });

        const topupChart = new Chart(document.getElementById('topupChart'), {
            type: 'pie',
            data: {
                labels: " . json_encode(array_keys($topupData)) . ",
                datasets: [{
                    label: 'Top-Ups',
                    data: " . json_encode(array_values($topupData)) . ",
                    backgroundColor: ['#f39c12', '#d35400', '#2ecc71', '#e74c3c', '#3498db']
                }]
            }
        });
    </script>";
}
   
    $topupQuery = new MongoDB\Driver\Query($filter);
    $topupResult = $mongo->executeQuery("$database.topups", $topupQuery);

    $topupData = [];
    foreach ($topupResult as $topup) {
        $userEmail = $topup->email ?? 'Unknown';
        $topupData[$userEmail] = ($topupData[$userEmail] ?? 0) + ($topup->amount ?? 0);
    }

  
    $totalFare = array_sum($fareData);
    echo "<p><strong>Total Fare Collected from " . date('Y-m-d', $from) . " to " . date('Y-m-d', $to - 86400) . ": ₹{$totalFare}</strong></p>";



?>


  <br>
  <a href="../api/logout.php">Logout</a>
</body>
</html>
