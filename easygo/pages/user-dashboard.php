<?php include('../api/session-check.php'); ?>
<?php require_once '../config/db.php'; ?>

<?php
$query = new MongoDB\Driver\Query(['email' => $_SESSION['user']]);
$result = $mongo->executeQuery($usersCollection, $query);
$user = current($result->toArray());


$tripQuery = new MongoDB\Driver\Query([
    'rfid_uid' => $user->rfid_uid ?? '',

    'action' => 'tap_out'
]);
$trips = $mongo->executeQuery("$database.transactions", $tripQuery)->toArray();


$topupQuery = new MongoDB\Driver\Query(['email' => $user->email]);
$topups = $mongo->executeQuery("$database.topups", $topupQuery)->toArray();
?>

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
            
          
            <!--header002 section-->
         
                <a href="user-dashboard.php">
                    <div class="navigation001" >
                        Profile
                    </div>
                </a>
                <a href="live-view.php">
                    <div class="navigation002">
                        Live-View 
                    </div>
                </a>
                <a href="top-up.php">
                    <div class="navigation002">
                        Top-Up 
                    </div>
                </a>
         
        </header>

<body>
  <!--using image as a background-->
  <div style="background-image: url(../images/sample.jpg); height:400px;width:100%; background-repeat: no-repeat;padding-top:100px; padding-bottom: 100px; background-attachment: fixed;">
            <div style="height: 350px;margin-left: 170px; width: 400px; float: left; padding-top: 40px; padding-left: 50px; background-color: #32333D; opacity: 80%;">
                
                    <div style="margin-left: 130px; height:40px; width: 300px; opacity: 100%; color:#FFF; font-family: 'Times New Roman', Times, serif; font-size: 20px;font-weight: 300;">
                        USER-INFO
                    </div>
                    <p style="color: #FFF; margin-left: 30px;">Name: 
                    <input type="text" name="name" style="margin-left: 48px;margin-top: 20px;" value="<?= $user->firstname . ' ' . $user->lastname ?>" readonly>
                    
                    <br>
                    <br>
                    Contact:
                    <input type="text" name="contact" style="margin-left: 38px;" value="<?= $user->contact ?>" readonly>
                
                    <br>
                    <br>
                    
                    Email:
                    <input type="text" name="email" style="margin-left: 48px;" value="<?= $user->email ?>" readonly>
        
                    <br>
                    <br>
                    Card ID:
                    <input type="text" name="cardid" style="margin-left: 35px;" value="<?= isset($user->rfid_uid) && $user->rfid_uid ? $user->rfid_uid : 'Not Assigned' ?>" readonly>
             
                    <br>
                    <br>
                    Card Status:
                    <input type="text" name="cardstatus" style="margin-left: 12px;" value="<?= $user->status ?>" readonly>
                  
                    <br>
                    <br>
                    Card Balance:
                    <input type="text" name="cardbalance" value="<?= $user->balance ?>" readonly>
                    <br>
                    <br>
                    </p>
                    <form method="POST" action="../api/block-card.php">
                        <input type="hidden" name="email" value="<?= $user->email ?>">
                        <input type="submit" value="BLOCK CARD" style="margin-left:150px; background-color: #5f0707; color:#FFF">
                        </form>
                </div>
                <div style="height: 350px;margin-right: 170px; width: 600px;float: right; margin: top 50px;  padding-top: 40px; padding-left: 50px; ">
                    <div style="margin-left: 180px; height:40px; width: 300px; opacity: 100%; color:#FFF; font-family: 'Times New Roman', Times, serif; font-size: 20px;font-weight: 300;">
                        TRAVEL HISTORY
                    </div>
 


                    <?php

$allTripsQuery = new MongoDB\Driver\Query(['rfid_uid' => $user->rfid_uid ?? ''], [
    'sort' => ['timestamp' => 1]
]);
$allTrips = $mongo->executeQuery("$database.transactions", $allTripsQuery)->toArray();


$pairedTrips = [];
$start = null;
foreach ($allTrips as $trip) {
    if ($trip->action == 'tap_in') {
        $start = $trip;
    } elseif ($trip->action == 'tap_out' && $start !== null) {
        $pairedTrips[] = [
            'start_time' => $start->timestamp->toDateTime(),
            'end_time' => $trip->timestamp->toDateTime(),
            'start_lat' => $start->lat ?? '-',
            'start_lng' => $start->lng ?? '-',
            'end_lat' => $trip->lat ?? '-',
            'end_lng' => $trip->lng ?? '-',
            'fare' => $trip->fare ?? '0'
        ];
        $start = null; 
    }
}
?>

<table style="margin-left:10px; color:#FFF;">
  <tr>
    <th>Date</th>
    <th>Start Location</th>
    <th>End Location</th>
    <th>Fare</th>
  </tr>
  <?php foreach ($pairedTrips as $trip): ?>
  <tr>
  <?php
$start = $trip['start_time'];
$start->setTimezone(new DateTimeZone('Asia/Kathmandu'));
?>
    <td><?= $trip['start_time']->format('Y-m-d H:i') ?></td>
    <td><?= $trip['start_lat'] . ', ' . $trip['start_lng'] ?></td>
    <td><?= $trip['end_lat'] . ', ' . $trip['end_lng'] ?></td>
    <td>NPR<?= $trip['fare'] ?></td>
  </tr>
  
  <?php endforeach; ?>
</table>

 
  <br>


  <h3 style="margin-left:200px; color:#FFF;">Top-Up History</h3><br>
  <?php if (count($topups) > 0): ?>
  <table style="margin-left: 10px; color:#FFF;">
    <tr>
      <th>Date</th>
      <th>Amount</th>
      <th>Method</th>
    </tr>
    <?php foreach ($topups as $top): ?>
    <tr>
      <td><?= date('Y-m-d H:i', $top->timestamp->toDateTime()->getTimestamp()) ?></td>
      <td>NPR<?= $top->amount ?? '0' ?></td>
      <td><?= $top->payment_method ?? 'Khalti' ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php else: ?>
    <p style="color:#FFF;">No top-up history found.</p>
  
  <?php endif; ?>
  </div>
  </div>
  <!--footer section-->
  <footer style="width:100%; height:70px;  background-color:#32333D; clear:both; ">
            <div class="footer001">
                CONTACT US:
            </div>
            <div>
                <p class="footer002">
                    <a href="https://www.gmail.com" target="_blank">
                        <img src="../images/mail.png" height="10px" width="20px" alt="mail"/> 
                        easygo.nepal@gmail.com 
                    </a>
                    <br>
                    <a href="https://www.viber.com" target="_blank">
                        <img src="../images/phone1.png" height="10px" width="20px" alt="phone"/> 
                        +977 9818000000
                    </a>
                </p>
          
            </div>
            <div style="padding-top: 20px;">
                <div class="footer001and1">
                    <a href="https://facebook.com" target="_blank">
                    <img src="../images/facebook.png" alt="facebook" height="15px" width="20px"/>
                    </a>
                </div>
                <div class="footer001and2">
                    <a href="https://instagram.com" target="_blank">
                    <img src="../images/instagram.png" alt="instagram" height="15px" width="20px"/>
                    </a>
                </div>
                <div class="footer001and2">
                    <a href="https://twitter.com" target="_blank">
                    <img src="../images/twitter.png" alt="twitter" height="15px" width="20px"/>
                    </a>
                </div>
                <div class="footer001and4">
                    Follow US On:
                </div>
            </div>
        </footer>
    </body>
</html>



