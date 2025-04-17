<?php include('../api/session-check.php'); ?>

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
            
           
            <!--header002 section-->
            <nav>
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
            </nav>
        </header>
        <!--using image as a background-->
        <div style="background-image: url(../images/sample.jpg); height:400px;width:100%; background-repeat: no-repeat;padding-top:100px; padding-bottom: 100px; background-attachment: fixed;">
      
            <div id="map" style="height: 400px; width:1000px;"></div>
              <script>
                let map = L.map('map').setView([27.7, 85.3], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                let marker = L.marker([27.7, 85.3]).addTo(map);

                function updateLocation() {
                  fetch('../api/get-location.php')
                    .then(res => res.json())
                    .then(data => {
                      marker.setLatLng([data.lat, data.lng]);
                      map.setView([data.lat, data.lng]);
                    });
                }

                  setInterval(updateLocation, 3000);
                  updateLocation();
                </script>
                <br>
             
      
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
