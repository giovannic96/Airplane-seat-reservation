<?php
require_once('utility.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['seat_number']) && !empty($_POST['seat_number'])) {
        
        $seat = $_POST['seat_number'];
        $seatSanitized = mySanitizeString($seat);

        if(isSeatValid($seatSanitized)) {
            $retVal = tryReservation($seatSanitized);
            $_SESSION['time'] = time();
            echo $retVal;
        } else {
            echo SEAT_NOT_VALID;
        }
    } else {
        echo RESERVATION_FAILED;
    }
} else {
    echo RESERVATION_FAILED;
}
?>