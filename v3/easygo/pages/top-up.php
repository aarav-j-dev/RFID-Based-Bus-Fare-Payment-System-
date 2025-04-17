<?php include('../api/session-check.php'); ?>

<!DOCTYPE html>
<html lang="en">
    <!--In this file, the css has been mentioned with suitable codes to identify them easily for external linking-->
    <head>
        <title>
            EasyGO
        </title>
        <script src="https://khalti.com/static/khalti-checkout.js"></script>
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
            <div style="height:300px;width:400px;background-color:#D9D9D9; opacity: 80%;border-radius: 60px; padding-top: 60px;">
                <div style="background-color: #944C4C; height:10px; width:100%;">
                </div>
                <div style="background-color: #944C4C; margin-top: 40px; border-radius: 50px; height:30px; width:150px; padding-top: 5px; box-sizing: border-box;">
                    <h4 style="color:#FFF; text-align: center;">TOP-UP CREDITS</h4>
                </div>
                <div style="margin-top: 50px; margin-left: 45px;">
                <form id="topup-form" onsubmit="return false;">
                  
                        Amount:
                        <input type="number" id="amount" value="10" readonly required style="margin-left: 25px;">
                        <br><br><br>
                        <button type="button" onclick="payWithKhalti()" style="margin-left:110px; background-color: #053209; color:#FFF">Pay via Khalti</button>
                    </form>

            
  <script>
    var config = {
      publicKey: "test_public_key_dc74e0fd57cb46cd93832aee0a390234",
      productIdentity: "1234567890",
      productName: "EasyGo Top-Up",
      productUrl: "http://localhost",
      paymentPreference: ["KHALTI"],
      eventHandler: {
        onSuccess(payload) {
          
          fetch("../api/khalti-verify.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              token: payload.token,
              amount: 1000,
              email: "<?= $_SESSION['user'] ?>"
            })
          })
          .then(res => res.json())
          .then(data => {
            window.location.href = "user-dashboard.php";
          });
        },
        onError(error) {
       
          fetch("../api/khalti-verify.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
              token: "SIMULATED_ERROR_TOKEN",
              amount: 1000,
              email: "<?= $_SESSION['user'] ?>"
            })
          })
          .then(res => res.json())
          .then(data => {
            window.location.href = "user-dashboard.php";
          });
        },
        onClose() {
          console.log("Khalti Checkout widget closed.");
        }
      }
    };

    var checkout = new KhaltiCheckout(config);

    function payWithKhalti() {
      checkout.show({ amount: 1000 }); 
    }
  </script>

  <br><a href="user-dashboard.php" style = "margin-left:80px;">Back to Dashboard</a>
  </div>
  </div>
        <!--footer section-->
        <footer style="width:100%; height:70px;  background-color:#32333D; clear:both;margin-top:110px; ">
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

