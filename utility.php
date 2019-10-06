<?php
define("REGISTER_OK", "Registrazione avvenuta con successo.");
define("LOGIN_OK", "Login effettuato.");
define("RESERVATION_OK", "Posto correttamente prenotato.");
define("PURCHASE_OK", "Posti correttamente acquistati.");
define("REGISTER_FAILED", "Errore durante la fase di registrazione.");
define("LOGIN_FAILED", "Errore durante la fase di login.");
define("RESERVATION_FAILED", "Errore durante la fase di prenotazione.");
define("PURCHASE_FAILED", "Errore durante la fase di acquisto.");
define("USER_ALREADY_EXIST", "Username già esistente.");
define("DB_ERROR", "Errore durante il collegamento al database.");
define("DB_QUERY_ERROR", "Errore durante una query al database.");
define("PASSWORD_INCORRECT", "La password che hai inserito non è corretta.");
define("EMAIL_INCORRECT", "L'email che hai inserito non è corretta.");
define("LOGIN_NOT_MATCH", "Username o password non validi.");
define("SEAT_NOT_VALID", "Posto selezionato non valido.");
define("SEAT_NOT_ALL_AVAILABLE", "Ci dispiace, alcuni posti che hai selezionato sono stati occupati da un altro utente");
define("SEAT_WAS_PURCHASED", "purchased");
define("SEAT_WAS_BOOKED", "booked");
define("SEAT_WAS_BOOKED_BY_ME", "booked-by-me");
define("SEAT_WAS_FREE", "free");
define("SESSION_EXPIRED", "session-expired");
define("MAX_INACTIVITY", 120);
define("CABIN_WIDTH", 6);
define("CABIN_HEIGHT", 10);

$alphabet = range('A','Z');

function connect_to_db() {
    return mysqli_connect("localhost", "root", "", "airplane_reservations"); //returns FALSE on error
}

function myDestroySession() {
    $_SESSION = array();
    if(ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time()-3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

function userLoggedIn() {
    if(isset($_SESSION['mySession']))
        return $_SESSION['mySession'];
    else 
        return false;
}

function myRedirectHome($msg="") {
    header('HTTP/1.1 307 temporary redirect');
    header("Location: index.php?msg=".urlencode($msg));
    exit;
}

function myRedirectTo($toRedirect, $msg="") {
    header('HTTP/1.1 307 temporary redirect');
    header('Location: '.$toRedirect.'?msg='.urlencode($msg));
    exit;
}

function myRedirectToHTTPS($toRedirect) {
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: '.$toRedirect);
}

function checkPassword($pwd) {
    return strlen($pwd) >= 2 && preg_match("#[a-z]+#", $pwd) && (preg_match("#[0-9]+#", $pwd) || preg_match("#[A-Z]+#", $pwd));
}

function checkPasswords($pwd, $pwd2) {
    return $pwd==$pwd2 && strlen($pwd) >= 2 && preg_match("#[a-z]+#", $pwd) && (preg_match("#[0-9]+#", $pwd) || preg_match("#[A-Z]+#", $pwd));
}

function checkEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function haveSameElements($a, $b) {
    return count(array_diff(array_merge($a, $b), array_intersect($a, $b))) === 0;
}

function mySanitizeString($var) {
	$var = strip_tags($var); //remove all HTML and PHP tag, and also NULL characters
    $var = htmlentities($var); //convert all special characters in HTML entities
    if(get_magic_quotes_gpc()) 
        $var = stripslashes($var); //remove backslashes
    return $var;
}

function mySanitizeSeatsJson($var) {
    $var = html_entity_decode($var); //"2A,2B"
    return filter_var($var, FILTER_SANITIZE_STRING); //strip tags, optionally strip or encode special characters.
}

function isSeatValid($str) {
    if(ctype_alnum($str) && preg_match('/^\d+[a-zA-Z]$/', $str)) { //alphanum && numbersFollowedByALetter
        $numbers = preg_replace('/[^0-9]/', '', $str);
        $letter = preg_replace('/[^a-zA-Z]/', '', $str);
        global $alphabet; //reference to global variable 'alphabet'

        if($numbers[0] != "0" && (int)$numbers <= CABIN_HEIGHT && array_search($letter, $alphabet) < CABIN_WIDTH) 
            return true;
    }
    return false;
}

function areSeatsValid($strArr) {
    if(count($strArr) <= 0 || count($strArr) > CABIN_HEIGHT*CABIN_WIDTH)
        return false;
    foreach($strArr as $s) {
        if(!isSeatValid($s))
            return false;
    }
    return true;
}

function trySignup($username, $password) {
    $con = connect_to_db();
    if($con && mysqli_connect_error() == NULL) {
        mysqli_autocommit($con, FALSE);
        try {
            if(!$prep = mysqli_prepare($con, "SELECT * FROM `users` WHERE email = ? FOR UPDATE"))
                throw new Exception();
            if(!mysqli_stmt_bind_param($prep, "s", $username)) //in this way, $username will be considered as a STRING! Perfect (no SQL injection)
                throw new Exception();
            if(!mysqli_stmt_execute($prep))
                throw new Exception();
            if(!mysqli_stmt_store_result($prep))
                throw new Exception();

            $count = mysqli_stmt_num_rows($prep);
            mysqli_stmt_free_result($prep);
            mysqli_stmt_close($prep);

            if($count == 1) {
                mysqli_rollback($con);
                mysqli_autocommit($con, TRUE);
                mysqli_close($con);
                return USER_ALREADY_EXIST;
            }
            else {
                if(!$prep2 = mysqli_prepare($con, "INSERT INTO `users` (`email`, `password`) VALUES (? , ?)"))
                    throw new Exception();
                if(!mysqli_stmt_bind_param($prep2, "ss", $username, $password)) 
                    throw new Exception();
                if(!mysqli_stmt_execute($prep2)) 
                    throw new Exception();
                else { 
                    mysqli_stmt_close($prep2);
                    if(!mysqli_commit($con)) // do the final commit
                        throw new Exception();
                    mysqli_autocommit($con, TRUE);
                    mysqli_close($con);
                    return REGISTER_OK;
                }
            }
        } catch (Exception $e) {
            mysqli_rollback($con);
            mysqli_autocommit($con, TRUE);
            mysqli_close($con);
            return REGISTER_FAILED;
        }
    } else {
        return DB_ERROR;
    }
}

function tryLogin($username, $password) {
    $con = connect_to_db();
    if($con && mysqli_connect_error() == NULL) {
        try {
            if(!$prep = mysqli_prepare($con, "SELECT password FROM `users` WHERE email = ?")) 
                throw new Exception();
            if(!mysqli_stmt_bind_param($prep, "s", $username)) 
                throw new Exception();
            if(!mysqli_stmt_execute($prep)) 
                throw new Exception();
            if(!mysqli_stmt_bind_result($prep, $dbPass))
                throw new Exception();  
            if(!mysqli_stmt_store_result($prep))
                throw new Exception();

            $count = mysqli_stmt_num_rows($prep);
            if($count == 0) { //email not found in db
                mysqli_stmt_close($prep);
                mysqli_close($con);
                return LOGIN_NOT_MATCH;
            } else {
                if(!mysqli_stmt_fetch($prep))
                    throw new Exception(); 
                if(password_verify($password, $dbPass)) { //password not correct 
                    mysqli_stmt_close($prep);
                    mysqli_close($con);
                    return LOGIN_OK;
                } else {
                    mysqli_stmt_close($prep);
                    mysqli_close($con);
                    return LOGIN_NOT_MATCH;
                }
            }
        } catch (Exception $e) {
            mysqli_close($con);
            return LOGIN_FAILED;
        }
    } else {
        return DB_ERROR;
    }
}

function tryPurchase($seats) {
    session_start();
    if(check_inactivity() == -1)
        return SESSION_EXPIRED;
    $con = connect_to_db();
    if($con && mysqli_connect_error() == NULL) {
        mysqli_autocommit($con, FALSE);
        try {
            $booked_str = SEAT_WAS_BOOKED;
            /* Get all seats booked by me */
            if(!$prep = mysqli_prepare($con, "SELECT seat FROM `reservations` WHERE status = ? AND user = ? FOR UPDATE"))
                throw new Exception();
            if(!mysqli_stmt_bind_param($prep, "ss", $booked_str, $_SESSION['mySession']))
                throw new Exception();
            if(!mysqli_stmt_execute($prep))
                throw new Exception();
            if(!mysqli_stmt_store_result($prep)) 
                throw new Exception();
            if(!mysqli_stmt_bind_result($prep, $theSeats))
                throw new Exception();
                                                    
            $count = mysqli_stmt_num_rows($prep);
            $seatsFromDb = array();
            while(mysqli_stmt_fetch($prep))
                $seatsFromDb[] = $theSeats;
            mysqli_stmt_free_result($prep);

            /* if ALL selected (from db) seats were booked by me -> purchase */
            if($count == count($seats) && haveSameElements($seatsFromDb, $seats)) { 
                mysqli_stmt_close($prep);
                $purchasedString = SEAT_WAS_PURCHASED;
                if(!$prep1 = mysqli_prepare($con, "UPDATE `reservations` SET status = ? WHERE seat = ? AND user = ?"))
                    throw new Exception();

                foreach($seatsFromDb as $s) {
                    if(!mysqli_stmt_bind_param($prep1, "sss", $purchasedString, $s, $_SESSION['mySession']))
                        throw new Exception();
                    if(!mysqli_stmt_execute($prep1)) 
                        throw new Exception();
                }
                mysqli_stmt_close($prep1);
                if(!mysqli_commit($con)) 
                    throw new Exception();
                mysqli_autocommit($con, TRUE);
                mysqli_close($con);
                return PURCHASE_OK; //then in JS seat color will become red 
            } else { 
                /* Not all seats were booked by me, then check if others are all available (green) */
                $remainingSeats = array_diff($seats, $seatsFromDb); 
                $counter = 0; //this variable will tell me if there are some seats (of the remainingSeats array) in db 

                if(!$prep3 = mysqli_prepare($con, "SELECT * FROM `reservations` WHERE seat = ? LIMIT 1 FOR UPDATE"))
                    throw new Exception();
                
                foreach($remainingSeats as $rs) {
                    if(!mysqli_stmt_bind_param($prep3, "s", $rs))
                        throw new Exception();
                    if(!mysqli_stmt_execute($prep3)) 
                        throw new Exception();
                    if(!mysqli_stmt_store_result($prep3)) 
                        throw new Exception();     
                    $counter += mysqli_stmt_num_rows($prep3); //increment variable by 1 (because of the LIMIT 1) or 0     
                    mysqli_stmt_free_result($prep3);
                }

                if($counter == 0) { //then ALL remaining seats were green (maybe they were freed by another user) -> purchase

                    //UPDATE (if present) seats booked by me from db 
                    $purchasedString1 = SEAT_WAS_PURCHASED;
                    if(count($seatsFromDb) > 0) { 
                        if(!$prep4 = mysqli_prepare($con, "UPDATE `reservations` SET status = ? WHERE seat = ? AND user = ?"))
                            throw new Exception();

                        foreach($seatsFromDb as $s) {
                            if(!mysqli_stmt_bind_param($prep4, "sss", $purchasedString1, $s, $_SESSION['mySession']))
                                throw new Exception();
                            if(!mysqli_stmt_execute($prep4)) 
                                throw new Exception();
                        }
                        mysqli_stmt_close($prep4);
                    }

                    //INSERT all the remaining seats that I want to purchase
                    if(!$prep5 = mysqli_prepare($con, "INSERT INTO `reservations` (`seat`, `status`, `user`) VALUES (? , ? , ?)"))
                        throw new Exception();

                    foreach($remainingSeats as $rs) {
                        if(!mysqli_stmt_bind_param($prep5, "sss", $rs, $purchasedString1, $_SESSION['mySession']))
                            throw new Exception();
                        if(!mysqli_stmt_execute($prep5)) 
                            throw new Exception();
                    }
                    mysqli_stmt_close($prep3);
                    mysqli_stmt_close($prep);
                    mysqli_stmt_close($prep5); 

                    if(!mysqli_commit($con)) 
                        throw new Exception();
                    mysqli_autocommit($con, TRUE);
                    mysqli_close($con);
                    return PURCHASE_OK; 
                } 
                else { //then AT LEAST one of the remaining seats was purchased or booked by another user -> delete my reservations
                    if(!$prep2 = mysqli_prepare($con, "DELETE FROM `reservations` WHERE seat = ?"))
                        throw new Exception();
                    
                    foreach($seatsFromDb as $s) {
                        if(!mysqli_stmt_bind_param($prep2, "s", $s))
                            throw new Exception();
                        if(!mysqli_stmt_execute($prep2)) 
                            throw new Exception();
                    }
                    mysqli_stmt_close($prep2);
                    if(!mysqli_commit($con)) 
                        throw new Exception();
                    mysqli_autocommit($con, TRUE);
                    mysqli_close($con);
                    return SEAT_NOT_ALL_AVAILABLE; // then in JS seat color will become green
                }
            }
        } catch (Exception $e) {
            mysqli_rollback($con);
            mysqli_autocommit($con, TRUE);
            mysqli_close($con);
            return PURCHASE_FAILED;
        }
    } else {
        return DB_ERROR;
    }
}

function tryReservation($seat) {
    session_start();
    if(check_inactivity() == -1)
        return SESSION_EXPIRED;
    $con = connect_to_db();
    if($con && mysqli_connect_error() == NULL) {
        mysqli_autocommit($con, FALSE);
        try {
            $bookedStr = SEAT_WAS_BOOKED;
            if(!$prep = mysqli_prepare($con, "SELECT * FROM `reservations` WHERE seat = ? AND status = ? AND user = ? FOR UPDATE"))
                throw new Exception();
            if(!mysqli_stmt_bind_param($prep, "sss", $seat, $bookedStr, $_SESSION['mySession']))
                throw new Exception();
            if(!mysqli_stmt_execute($prep))
                throw new Exception();
            if(!mysqli_stmt_store_result($prep)) 
                throw new Exception();
            
            $count = mysqli_stmt_num_rows($prep);
            mysqli_stmt_free_result($prep);
            mysqli_stmt_close($prep);

            if($count == 1) { //it was me, so I have to delete my previous reservation
                if(!$prep4 = mysqli_prepare($con, "DELETE FROM `reservations` WHERE seat = ? AND status = ? AND user = ?"))
                    throw new Exception();
                if(!mysqli_stmt_bind_param($prep4, "sss", $seat, $bookedStr, $_SESSION['mySession']))
                    throw new Exception();
                if(!mysqli_stmt_execute($prep4)) 
                    throw new Exception();
                else { 
                    mysqli_stmt_close($prep4);
                    if(!mysqli_commit($con)) // do the final commit
                        throw new Exception();
                    mysqli_autocommit($con, TRUE);
                    mysqli_close($con);
                    return SEAT_WAS_BOOKED_BY_ME; // then in JS seat color will become green
                }
            } else { //it wasn't a previous reservation of mine, so continue other checks
                if(!$prep1 = mysqli_prepare($con, "SELECT status FROM `reservations` WHERE seat = ? FOR UPDATE"))
                    throw new Exception();
                if(!mysqli_stmt_bind_param($prep1, "s", $seat))
                    throw new Exception();
                if(!mysqli_stmt_execute($prep1))
                    throw new Exception();
                if(!mysqli_stmt_bind_result($prep1, $status)) 
                    throw new Exception();
                $data_fetched = mysqli_stmt_fetch($prep1);

                if($data_fetched === FALSE) //IMPORTANT: put === to check if is 'real false', otherwise NULL will be also treated as false
                    throw new Exception();
                if(!is_null($data_fetched)) { //seat exists, check status
                    mysqli_stmt_close($prep1);
                    if($status == SEAT_WAS_PURCHASED) {
                        if(!mysqli_commit($con)) // do the final commit
                            throw new Exception();
                        mysqli_autocommit($con, TRUE);
                        mysqli_close($con);
                        return SEAT_WAS_PURCHASED; //then in JS seat color will become red and JS has to add class 'disabled' (in order to stop animation)
                    }
                    else { //now seat will be assigned to the last user
                        if(!$prep2 = mysqli_prepare($con, "UPDATE `reservations` SET user = ? WHERE seat = ?"))
                            throw new Exception();
                        if(!mysqli_stmt_bind_param($prep2, "ss", $_SESSION['mySession'], $seat))
                            throw new Exception();
                        if(!mysqli_stmt_execute($prep2)) 
                            throw new Exception();
                        else { 
                            mysqli_stmt_close($prep2);
                            if(!mysqli_commit($con)) // do the final commit
                                throw new Exception();
                            mysqli_autocommit($con, TRUE);
                            mysqli_close($con);
                            return SEAT_WAS_BOOKED; // then in JS seat color will become yellow
                        }
                    }
                } else { //seat does not exists, insert new reservation record
                    mysqli_stmt_close($prep1);
                    $bookedString = SEAT_WAS_BOOKED;
                    if(!$prep3 = mysqli_prepare($con, "INSERT INTO `reservations` (`seat`, `status`, `user`) VALUES (? , ? , ?)"))
                        throw new Exception();
                    if(!mysqli_stmt_bind_param($prep3, "sss", $seat, $bookedString, $_SESSION['mySession']))
                        throw new Exception();
                    if(!mysqli_stmt_execute($prep3)) 
                        throw new Exception();
                    else { 
                        mysqli_stmt_close($prep3);
                        if(!mysqli_commit($con)) // do the final commit
                            throw new Exception();
                        mysqli_autocommit($con, TRUE);
                        mysqli_close($con);
                        return SEAT_WAS_FREE; //then in JS seat color will become yellow 
                    }
                }
            }
        } catch (Exception $e) {
            mysqli_rollback($con);
            mysqli_autocommit($con, TRUE);
            mysqli_close($con);
            return RESERVATION_FAILED;
        }
    } else {
        return DB_ERROR;
    }
}

function loadReservations() {
    if(!isset($_SESSION))
        session_start();
    $con = connect_to_db();
    if($con && mysqli_connect_error() == NULL) {
        $query = "SELECT seat, status, user FROM reservations";

        if(!$result = mysqli_query($con, $query)) {
            $_SESSION['msg_result'] = DB_QUERY_ERROR;
            mysqli_close($con);
            return 0;
        }
        $count = mysqli_num_rows($result);
        if($count == 0) {
            mysqli_close($con);
            return $count;
        } else {
            while($sql_row = mysqli_fetch_assoc($result))
                $seatsArray[] = $sql_row;
            mysqli_free_result($result);
            mysqli_close($con);
            return $seatsArray;
        }
    } else 
        return DB_ERROR;
}

function check_inactivity () {
    if(!isset($_SESSION))
        session_start();
	$t = time();
	$diff = 0;
    $new = false;
    
	if(isset($_SESSION['time'])) {
		$t0 = $_SESSION['time'];
		$diff = ($t - $t0); 
	}
	else {
		$new = true;
	}
	if ($new || ($diff > MAX_INACTIVITY)) {
		$_SESSION = array(); //in this way I delete session variable (initializing it to a new array) but ID remains for the next session!

		if(ini_get("session.use_cookies")) { //to kill the session, delete also session cookie! (by setting it to a past expiry time)
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 3600*24, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        //By using the above mechanism, the next session does NOT see the cookie, and so it will give a new ID for the session :)
        session_destroy(); 
		if ($new) { 
			header('HTTP/1.1 307 temporary redirect');
			header('Location: index.php');
		}
        else { //Redirect client to login page
            return -1;
		}
		exit; //IMPORTANT to avoid further output from the script
	}
	else {
        $_SESSION['time'] = time(); //Update time
	}
}

?>