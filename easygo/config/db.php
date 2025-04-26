<?php
date_default_timezone_set('Asia/Kathmandu');
$mongo = new MongoDB\Driver\Manager("mongodb://localhost:27017");
$database = "easygo";
$usersCollection = "$database.users";
$transactionsCollection = "$database.transactions";
?>
