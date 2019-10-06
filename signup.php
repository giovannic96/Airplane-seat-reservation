<?php
require_once('utility.php');

/* HTTPS CHECK */
if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
} else { 
  $redirectHTTPS = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  myRedirectToHTTPS($redirectHTTPS);
  exit;
}
session_start();
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
          <div class="w3-white w3-text-grey w3-card-2 w3-round-large">
            <div class="w3-container">
              <p class="w3-xlarge"><b><i class="fa fa-home fa-fw w3-margin-right main-color"></i><a href="index.php">Home</a></b></p>
              <p class="w3-xlarge"><b><i class="fa fa-sign-in-alt fa-fw w3-margin-right main-color"></i><a href="login.php">Login</a></b></p></b></p>
              <p class="w3-xlarge"><b><i class="fa fa-user-plus fa-fw w3-margin-right main-color"></i><a href="signup.php">Registrazione</a></b></p>
            </div>
          </div><br>
        </aside>
        <div class="w3-threequarter">
          <div class="w3-container w3-card w3-white w3-margin-bottom w3-round-large">
            <h2 class="w3-text-grey w3-padding-16"><i class="fa fa-user-plus fa-fw w3-margin-right w3-xxlarge main-color"></i>Registrazione</h2>
            <form name="login-form" class="login-form" action="registration.php" method="post" onsubmit="return validateForm()">
                <div class="w3-container">
                    <input class="input-form w3-margin-top w3-round-xxlarge w3-light-grey" type="email" name="username" placeholder="Nome utente" required>
                    <input id="password" class="input-form w3-margin-top w3-round-xxlarge w3-light-grey" type="password" name="password" placeholder="Password" pattern="(?=.*[a-z])(?=.*[A-Z\d]).+" title="La password deve contenere almeno un carattere alfabetico minuscolo, ed almeno un altro carattere che sia alfabetico maiuscolo oppure un carattere numerico" required>
                    <input id="confirm_password" class="input-form w3-margin-top w3-round-xxlarge w3-light-grey" type="password" name="confirm_password" placeholder="Ripeti Password" pattern="(?=.*[a-z])(?=.*[A-Z\d]).+" title="La password deve contenere almeno un carattere alfabetico minuscolo, ed almeno un altro carattere che sia alfabetico maiuscolo oppure un carattere numerico" required>
                    <?php 
                      if(isset($_SESSION['msg_result'])) {
                        if(!empty($_SESSION['msg_result'])) { ?>
                          <div class="w3-padding error-back-color w3-round-xxlarge w3-text-red"><span><b><?php echo $_SESSION['msg_result'];?></b></span></div></b></span></div>
                        <?php }
                        $_SESSION['msg_result'] = "";} ?>
                    <button class="my-input-btn my-margin-top main-color w3-margin-bottom w3-round-xxlarge" type="submit">Registrati</button>
                </div>
            </form>
          </div>
        </div> 
      </div>
    </div> 
    <footer class="w3-container w3-text-white main-back-color w3-left w3-margin-top">
      <p>&copy; 2019 &nbsp;| &nbsp;Giovanni Calà </p>
    </footer>
  </body>
</html>
