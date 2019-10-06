<?php
require_once('utility.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['seats_chosen']) && !empty($_POST['seats_chosen'])) {
        
        $seats = $_POST['seats_chosen']; //"2A,2B" (from JSON.stringify)
        $seatsSanitized = mySanitizeString($seats); //&quot;2A,2B&quot;
        $seatsSanitizedJson = mySanitizeSeatsJson($seatsSanitized); //&#34;2A,2B&#34;

        if($seatsSanitizedJson) {
            $seatsJson = json_decode(html_entity_decode($seatsSanitizedJson), true); //2A,2B
            if (json_last_error() === JSON_ERROR_NONE) { 
                $seatsArr = explode(',', $seatsJson); //[0: 2A] [1: 2B]
                if(areSeatsValid($seatsArr)) {                       
                    $retVal = tryPurchase($seatsArr);
                    if($retVal == PURCHASE_OK) { 
                        header("Content-Type: application/json", true);
                        if(count($seatsArr)==1)
                            $data = array("msg"=>"Il posto $seatsJson è stato correttamente acquistato.", 
                                    "url"=>"user_auth.php");
                        else 
                            $data = array("msg"=>"I posti $seatsJson sono stati correttamente acquistati.", 
                                    "url"=>"user_auth.php");
                        $dataArr[] = $data;
                        echo json_encode($dataArr);
                    }
                    else if($retVal == SEAT_NOT_ALL_AVAILABLE) {
                        header("Content-Type: application/json", true);
                        $data = array("msg"=>"Ci dispiace, alcuni posti che hai selezionato sono stati occupati da un altro utente. Riprovare", 
                                    "url"=>"user_auth.php");
                        $dataArr[] = $data;
                        echo json_encode($dataArr);
                    }
                    else if($retVal == DB_ERROR) {
                        header("Content-Type: application/json", true);
                        $data = array("msg"=>DB_ERROR, 
                                    "url"=>"user_auth.php");
                        $dataArr[] = $data;
                        echo json_encode($dataArr);
                    }
                    else if($retVal == SESSION_EXPIRED) {
                        header("Content-Type: application/json", true);
                        $data = array("msg"=>"Sessione scaduta. Per favore effettuare nuovamente il login.", 
                                    "url"=>"login.php");
                        $dataArr[] = $data;
                        echo json_encode($dataArr);
                    }
                    else {
                        header("Content-Type: application/json", true);
                        $data = array("msg"=>PURCHASE_FAILED, 
                                    "url"=>"user_auth.php");
                        $dataArr[] = $data;
                        echo json_encode($dataArr);
                    }
                } else {
                    header("Content-Type: application/json", true);
                    $data = array("msg"=>PURCHASE_FAILED, 
                                    "url"=>"user_auth.php");
                    $dataArr[] = $data;
                    echo json_encode($dataArr);
                }
            } else {
                header("Content-Type: application/json", true);
                $data = array("msg"=>PURCHASE_FAILED, 
                                "url"=>"user_auth.php");
                $dataArr[] = $data;
                echo json_encode($dataArr);
            }
        } else {
            header("Content-Type: application/json", true);
            $data = array("msg"=>PURCHASE_FAILED, 
                            "url"=>"user_auth.php");
            $dataArr[] = $data;
            echo json_encode($dataArr);
        }
    } else {
        header("Content-Type: application/json", true);
        $data = array("msg"=>PURCHASE_FAILED, 
                        "url"=>"user_auth.php");
        $dataArr[] = $data;
        echo json_encode($dataArr);
    }
} else {
    header("Content-Type: application/json", true);
    $data = array("msg"=>PURCHASE_FAILED, 
                    "url"=>"user_auth.php");
    $dataArr[] = $data;
    echo json_encode($dataArr);
}
?>