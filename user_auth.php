<?php
require_once('utility.php');

/* HTTPS CHECK */
if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
} else {
  $redirectHTTPS = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  myRedirectToHTTPS($redirectHTTPS);
  exit;
}
check_inactivity();
if(!isset($_SESSION)) 
  session_start();
 
/* LOGGED IN CHECK */
if(!userLoggedIn()) {   
  myRedirectTo('login.php', 'SessionTimeOut');
  exit;
}

$stats = array('0', '0', '0', '0');
?>
<!DOCTYPE html> 
<html>
  <head>
    <title>Prenotazione posti aereo</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="font.css">
    <link rel="stylesheet" type="text/css" href="w3.css">
    <link rel="stylesheet" type="text/css" href="style.css">
    <script src="jquery.js" type="text/javascript"></script>
    <script src="index.js"></script>
  </head>
  <body class="w3-light-grey" onload="checkCookiePresence()"> <!-- call the function immediately after a page has been loaded -->
    <header class="main-back-color">
      <h1 class="w3-text-white">Prenotazione posti</h1>
    </header>
    <noscript>
      <h4 id="no-js"><b>Attenzione: per visualizzare correttamente il contenuto della pagina occorre avere JavaScript abilitato.</b><h4>
    </noscript>
    <div id="content-wrapper" class="w3-content w3-margin-top" style="max-width:1400px;">
      <div class="w3-row-padding">
        <aside class="w3-quarter">
        <div class="w3-padding w3-white w3-text-grey w3-card-2 w3-round-large w3-margin-bottom">
            <div class="w3-row-padding">
              <div class="w3-half w3-container">
                <img src="avatar.png" alt="Avatar">
              </div>
              <div class="w3-half w3-container">
                <p id="avatar-name"><b><?php if(userLoggedIn()){echo $_SESSION['mySession'];} ?></b></p>
              </div>
            </div>  
          </div>
          <div class="w3-white w3-text-grey w3-card-2 w3-round-large">
            <div class="w3-container">
              <p class="w3-xlarge"><b><i class="fa fa-home fa-fw w3-margin-right main-color"></i><a href="user_auth.php">Home</a></b></p>
              <p class="w3-xlarge"><b><i class="fa fa-sign-out-alt fa-fw w3-margin-right main-color"></i><a href="logout.php">Logout</a></b></p>
            </div>
          </div><br>
          <div class="w3-padding w3-white w3-text-grey w3-card-2 w3-round-large w3-margin-bottom">
            <div class="w3-row-padding">
              <div class="w3-container">
                <input id="refresh-btn" type="button" class="w3-margin-top my-input-btn main-color w3-margin-bottom w3-round-xxlarge" value="Aggiorna">
              </div>
              <div class="w3-container">
                <input id="purchase-btn" type="submit" class="w3-margin-top my-input-btn main-color w3-margin-bottom w3-round-xxlarge" value="Acquista">
              </div>
            </div>
          </div>
          <span id="myspan"></span> <!-- Here there will be the stats-container -->
        </aside>
        <div class="w3-threequarter">
          <div class="w3-container w3-card w3-white w3-margin-bottom w3-round-large">
            <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-plane fa-fw w3-margin-right w3-xxlarge main-color"></i>Seleziona posti</h2>
            <?php
              $reservations = loadReservations();
              if ($reservations !== DB_ERROR) {
              for ($i=0; $i<CABIN_HEIGHT; $i++) { ?>
                 <ol class="seats" type="A"> <?php
                 for ($j=0; $j<CABIN_WIDTH; $j++) { ?>
                   <li class="seat">
                    <input id=<?php echo ($i+1).$alphabet[$j]; ?> type="checkbox"/>
                    <label class="<?php 
                      if($reservations) { 
                        foreach ($reservations as $reservation) {
                          $found = false;  
                          if($reservation['seat'] == ($i+1).$alphabet[$j] && $reservation['status'] == "booked") {
                            if($reservation['user'] == $_SESSION['mySession']) {
                              $status = "seat-booked-by-me"; $stats[2]++; $found = true; break; }
                            else {
                              $status = "seat-booked"; $stats[2]++; $found = true; break; }
                          }
                          else if($reservation['seat'] == ($i+1).$alphabet[$j] && $reservation['status'] == "purchased") {
                            $status = "seat-purchased"; $stats[1]++; $found = true; break;
                          }
                        }
                        if(!$found) $status = "seat-free";
                        $stats[0] = CABIN_HEIGHT*CABIN_WIDTH;
                        $stats[3] = $stats[0] - ($stats[1] + $stats[2]);
                        echo $status; 
                      } else echo "seat-free";
                      ?> w3-round w3-text-black" for=<?php echo ($i+1).$alphabet[$j]; ?>><?php echo ($i+1).$alphabet[$j]; ?></label>
                  </li> 
                 <?php } ?>
                 </ol> <?php
              } } else { ?><div class="w3-margin-top w3-margin-bottom w3-padding error-back-color w3-round-xxlarge w3-text-red"><span><b><?php echo DB_ERROR;?></b></span></div></b></span></div><?php
              }?>
          </div>
        </div> 
      </div>
      <!-- This will be moved in the correct place (left column of the page) by using JQuery -->
      <div id="stats-container" class="w3-white w3-text-grey w3-card-2 w3-round-large">
        <div class="w3-container">
          <p class="w3-large"><b><i class="fa fa-square fa-lg w3-text-black w3-margin-right main-color"></i>
          <label>Posti totali: </label></b><b><?php echo $stats[0]; ?></b></p>
          <p class="w3-large"><b><i class="fa fa-square fa-lg w3-text-red w3-margin-right main-color"></i>
          <label>Posti acquistati: </label></b></b><b><?php echo $stats[1]; ?></b></p></p>
          <p class="w3-large"><b><i class="fa fa-square fa-lg w3-text-orange w3-margin-right main-color"></i>
          <label>Posti prenotati: </label></b><b><?php echo $stats[2]; ?></b></p></p>
          <p class="w3-large"><b><i class="fa fa-square fa-lg w3-text-green w3-margin-right main-color"></i>
          <label>Posti liberi: </label></b><b><?php echo $stats[3]; ?></b></p></p>
        </div>
      </div>
    </div> 
    <footer class="w3-container w3-text-white main-back-color w3-left w3-margin-top">
      <p>&copy; 2019 &nbsp;| &nbsp;Giovanni Calà </p>
    </footer>
  </body>
</html>
