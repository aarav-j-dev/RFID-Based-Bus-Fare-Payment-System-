<?php
date_default_timezone_set('Asia/Kathmandu');
include('../api/session-check.php'); ?>
<?php if ($_SESSION['role'] !== 'admin') { header("Location: user-dashboard.php"); exit; } ?>
<?php require_once '../config/db.php'; ?>
<!DOCTYPE html>
<html lang="en">
    <!--In this file, the css has been mentioned with suitable codes to identify them easily for external linking-->
    <head>
        <title>
            EasyGO
        </title>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="login" content="login form">
        <!--border full resolutions-->
        <style type="text/css">
            *
            {
                margin: 0px auto;
            }
            a:link {color:rgb(255, 255, 255);}
            a:visited{color:rgb(255, 255, 255);}
            a:hover{color:rgb(198, 172, 106)}
            body{background-color:#ffffff}
        </style>
        <!--linking an external css file-->
        <link rel="stylesheet" href="../css files/style.css"/>
       <!-- Leaflet Map CSS & JS -->
       <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
       <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    </head>
    <body>
        <header>
          
            <!--header001 section-->
            <div class="header001"> 
                    
                <div class="header002">
                    <a href="home.html" >
                        <img src="../images/logo.png" alt="logo" height="80px" width="300px"/> 
                    </a>
                     
                    <a href="../api/logout.php" style="margin-left:800px">Logout</a>

                </div> 
            </div>
            
           
  
        </header>
        <!--using image as a background-->
    
<?php if (isset($_SESSION['message'])): ?>
    <div style="padding: 10px; margin: 10px 0; background-color: <?= $_SESSION['msg_type'] === 'success' ? '#d4edda' : '#f8d7da' ?>; color: <?= $_SESSION['msg_type'] === 'success' ? '#155724' : '#721c24' ?>; border: 1px solid <?= $_SESSION['msg_type'] === 'success' ? '#c3e6cb' : '#f5c6cb' ?>; border-radius: 5px;">
        <?= htmlspecialchars($_SESSION['message']) ?>
    </div>
    <?php unset($_SESSION['message'], $_SESSION['msg_type']); ?>
<?php endif; ?>
  <br><br> 
  <h2 style="margin-left:600px;">Admin Dashboard</h2>
  <br><br><br> 
  
  <form method="POST" action="">
    <h3>Add or Deduct Balance</h3><br>
    User Email: <input type="email" name="email" required>
    Amount (+  / - ): <input type="number" name="amount" required>
    <input type="submit" name="update_balance" value="Submit">
  </form>
  <br><br>

  <!-- Assign/Remove RFID -->
  <form method="POST" action="">
    <h3>Assign or Remove RFID Card</h3><br>
    User Email: <input type="email" name="email_rfid" required>
    RFID UID: <input type="text" name="rfid_uid">
    <input type="submit" name="assign_rfid" value="Assign/Update RFID">
    <input type="submit" name="remove_rfid" value="Remove RFID">
  </form>
  <br><br>

<?php

    if (isset($_POST['update_balance'])) {
        $email = $_POST['email'];
        $amount = (int)$_POST['amount'];

    
        $userCheck = new MongoDB\Driver\Query(['email' => $email]);
        $userResult = $mongo->executeQuery($usersCollection, $userCheck)->toArray();

        if (count($userResult) === 0) {
            $_SESSION['message'] = "User not found!";
            $_SESSION['msg_type'] = "error";
        } else {
            $user = $userResult[0];
            $newBalance = ($user->balance ?? 0) + $amount;

            if ($newBalance < 0) {
                $_SESSION['message'] = "Insufficient balance to deduct!";
                $_SESSION['msg_type'] = "error";
            } else {
            
                $bulk = new MongoDB\Driver\BulkWrite;
                $bulk->update(
                    ['email' => $email],
                    ['$inc' => ['balance' => $amount]]
                );
                $mongo->executeBulkWrite($usersCollection, $bulk);

            
                $log = new MongoDB\Driver\BulkWrite;
                $log->insert([
                    'email' => $email,
                    'action' => ($amount >= 0 ? 'admin_topup' : 'admin_deduction'),
                    'amount' => abs($amount),
                    'timestamp' => new MongoDB\BSON\UTCDateTime()
                ]);
                $mongo->executeBulkWrite("$database.topups", $log);

                $_SESSION['message'] = "Balance updated successfully.";
                $_SESSION['msg_type'] = "success";
            }
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }


    if (isset($_POST['assign_rfid']) && !empty($_POST['rfid_uid'])) {
        $email = $_POST['email_rfid'];
        $rfid = $_POST['rfid_uid'];

        $checkRFID = new MongoDB\Driver\Query(['rfid_uid' => $rfid]);
        $rfidResults = $mongo->executeQuery($usersCollection, $checkRFID)->toArray();

        if (count($rfidResults) > 0) {
            $_SESSION['message'] = "This RFID card has already been assigned to a user and cannot be reused.";
            $_SESSION['msg_type'] = "error";
        } else {
            $bulk = new MongoDB\Driver\BulkWrite;
            $bulk->update(
                ['email' => $email],
                ['$set' => ['rfid_uid' => $rfid]]
            );
            $mongo->executeBulkWrite($usersCollection, $bulk);
            $_SESSION['message'] = "RFID assigned successfully.";
            $_SESSION['msg_type'] = "success";
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
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
 <h2>Transaction Summary</h2><br>
 <form method="GET" style="margin-bottom: 20px;">
  From: <input type="date" name="from" required>
  To: <input type="date" name="to" required>
  <input type="submit" value="Apply Filter">
 </form>

<div style="display: flex; flex-wrap: wrap; gap: 20px;">
  <div>
  <canvas id="fareChart" width="250" height="250"></canvas>
  <div style="text-align:center; font-weight:bold;">Fare Collection</div>
</div>

<div>
  <canvas id="tripChart" width="250" height="250"></canvas>
  <div style="text-align:center; font-weight:bold;">Total Trips</div>

</div>

<div>
  <canvas id="topupChart" width="250" height="250"></canvas>
  <div style="text-align:center; font-weight:bold;">Top-Up Summary</div>
</div>

</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php

$rfidMap = [];
$userQuery = new MongoDB\Driver\Query([]);
$allUsers = $mongo->executeQuery($usersCollection, $userQuery);
foreach ($allUsers as $u) {
    if (isset($u->rfid_uid)) {
        $rfidMap[$u->rfid_uid] = $u->email;
    }
}

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
    foreach ($fareResult as $txn) {
        $id = $txn->email ?? ($rfidMap[$txn->rfid_uid] ?? 'Unknown');
        $fareData[$id] = ($fareData[$id] ?? 0) + ($txn->fare ?? 0);
    }

   
    $tripQuery = new MongoDB\Driver\Query($filter);
    $tripResult = $mongo->executeQuery("$database.transactions", $tripQuery);

    $tripData = [];
    foreach ($tripResult as $trip) {
        $id = $trip->email ?? ($rfidMap[$trip->rfid_uid] ?? 'Unknown');
        $tripData[$id] = ($tripData[$id] ?? 0) + 1;
    }

   
    $topupQuery = new MongoDB\Driver\Query($filter);
    $topupResult = $mongo->executeQuery("$database.topups", $topupQuery);

    $topupData = [];
    foreach ($topupResult as $topup) {
        $id = $topup->email ?? 'Unknown';
        $topupData[$id] = ($topupData[$id] ?? 0) + ($topup->amount ?? 0);
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

    if (isset($fareData) || isset($tripData) || isset($topupData)) {
        $totalFare = array_sum($fareData);
        $totalTrips = array_sum($tripData);
        $totalTopup = array_sum($topupData);
    
        echo "<br><br>";
        echo "<p><strong>Total Fare Collected from " . date('Y-m-d', $from) . " to " . date('Y-m-d', $to - 86400) . ": NPR {$totalFare}</strong></p>";
        echo "<p><strong>Total Trips: {$totalTrips}</strong></p>";
        echo "<p><strong>Total Top-Up: NPR {$totalTopup}</strong></p>";
    }
   

}
?>


  <!-- Search Users -->
  <br><br>
  <h3>Search User</h3><br>
  <form method="GET" action="">
    <input type="text" name="search_email" placeholder="Enter email to search">
    <input type="submit" value="Search">
  </form>
  <br><br>
  <h3>All Users</h3><br>
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
      <strong>Balance:</strong> NPR{$u->balance}<br>
      <form style='display:inline' method='POST' action='../api/toggle-card.php'>
        <input type='hidden' name='email' value='{$u->email}'>
        <input type='submit' value='" . ($u->status == 'active' ? 'Block' : 'Unblock') . "'>
      </form><br>";
     

      if (isset($u->rfid_uid)) {
       
        $travelQuery = new MongoDB\Driver\Query(
            ['rfid_uid' => $u->rfid_uid],
            ['sort' => ['timestamp' => -1], 'limit' => 10]
        );
        $records = iterator_to_array($mongo->executeQuery("$database.transactions", $travelQuery));
    
        echo "<br><strong>Recent Travel History:</strong>";
    
        if (count($records) === 0) {
            echo "<p>No history found.</p>";
        } else {
            echo "<ul>";
    
          
            $paired = [];
            $openTapIn = null;
    
            foreach (array_reverse($records) as $record) {
                $action = $record->action ?? '';
                
                $dt = $record->timestamp->toDateTime();
                $dt->setTimezone(new DateTimeZone('Asia/Kathmandu'));
                $time = $dt->format("Y-m-d H:i:s");
                $lat = $record->lat ?? 'Unknown';
                $lng = $record->lng ?? 'Unknown';
    
                if ($action === 'tap_in') {
                    $openTapIn = [
                        'time' => $time,
                        'lat' => $lat,
                        'lng' => $lng,
                        'fare' => 0
                    ];
                } elseif ($action === 'tap_out' && $openTapIn) {
                   
                    echo "<li>
                        <strong>Tap In:</strong> {$openTapIn['time']} at ({$openTapIn['lat']}, {$openTapIn['lng']})<br>
                        <strong>Tap Out:</strong> $time at ($lat, $lng)<br>
                        <strong>Fare:</strong> NPR{$record->fare}
                    </li><br>";
                    $openTapIn = null;
                }
            }
    
         
            if ($openTapIn) {
                $tapInTime = new DateTime($openTapIn['time'], new DateTimeZone('Asia/Kathmandu'));
                $now = new DateTime();
                $interval = $now->diff($tapInTime);
            
             
                if ($interval->h + ($interval->days * 24) >= 12) {
            
                    $existingFineQuery = new MongoDB\Driver\Query([
                        'rfid_uid' => $u->rfid_uid,
                        'action' => 'fine',
                        'tap_in_time' => new MongoDB\BSON\UTCDateTime($tapInTime->getTimestamp() * 1000)
                    ]);
            
                    $existingFines = $mongo->executeQuery("$database.transactions", $existingFineQuery)->toArray();
                    if (count($existingFines) === 0) {
            
                        $fineAmount = 100;
                        if ($u->balance >= $fineAmount) {
                         
                            $fineWrite = new MongoDB\Driver\BulkWrite;
                            $fineWrite->update(
                                ['email' => $u->email],
                                ['$inc' => ['balance' => -$fineAmount]]
                            );
                            $mongo->executeBulkWrite($usersCollection, $fineWrite);
            
                            $logFine = new MongoDB\Driver\BulkWrite;
                            $logFine->insert([
                                'rfid_uid' => $u->rfid_uid,
                                'email' => $u->email,
                                'action' => 'fine',
                                'amount' => $fineAmount,
                                'tap_in_time' => new MongoDB\BSON\UTCDateTime($tapInTime->getTimestamp() * 1000),
                                'timestamp' => new MongoDB\BSON\UTCDateTime()
                            ]);
                            $mongo->executeBulkWrite("$database.transactions", $logFine);
            
                            echo "<li style='color:orange;'>
                                <strong>Unmatched Tap In:</strong> {$openTapIn['time']} at ({$openTapIn['lat']}, {$openTapIn['lng']})<br>
                                <strong>Status:</strong> <em>Tap Out Missing (12+ hrs) - NPR100 Fine Deducted</em>
                            </li>";
            
                        } else {
                           
                            $blockWrite = new MongoDB\Driver\BulkWrite;
                            $blockWrite->update(
                                ['email' => $u->email],
                                ['$set' => ['status' => 'blocked']]
                            );
                            $mongo->executeBulkWrite($usersCollection, $blockWrite);
            
                            echo "<li style='color:red;'>
                                <strong>Unmatched Tap In:</strong> {$openTapIn['time']} at ({$openTapIn['lat']}, {$openTapIn['lng']})<br>
                                <strong>Status:</strong> <em>Tap Out Missing (12+ hrs) - Card Blocked (Insufficient Balance)</em>
                            </li>";
                        }
                    } else {
                     
                        echo "<li style='color:gray;'>
                            <strong>Unmatched Tap In:</strong> {$openTapIn['time']} at ({$openTapIn['lat']}, {$openTapIn['lng']})<br>
                            <strong>Status:</strong> <em>Fine Already Applied</em>
                        </li>";
                    }
            
                } else {
                  
                    echo "<li style='color:blue;'>
                        <strong>Unmatched Tap In:</strong> {$openTapIn['time']} at ({$openTapIn['lat']}, {$openTapIn['lng']})<br>
                        <strong>Status:</strong> <em>Tap Out Pending (Under 12 hrs)</em>
                    </li>";
                }
            }
            
             
            echo "</ul>";
          
        }
      
    }
    echo "<hr style='border: 1px solid #ccc; margin: 20px 0;'>";
}
?>
  </ul>
 

  <br>



</body>

</html>
