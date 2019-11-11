<?php
require_once('utility.php');
session_start();
header('Location: login.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['username']) && isset($_POST['password']) && !empty($_POST['username']) && !empty($_POST['password'])) {

        $username = $_POST['username'];
        $password = $_POST['password'];

        $isPasswordCorrect = checkPassword($password);
        $isEmailCorrect = checkEmail($username);

        if(!$isEmailCorrect)
            $_SESSION['msg_result'] = EMAIL_INCORRECT;
        else if(!$isPasswordCorrect)
            $_SESSION['msg_result'] = PASSWORD_INCORRECT;
        else {
            $username = mySanitizeString($username);
            $retVal = tryLogin($username, $password);
            if($retVal == LOGIN_OK) {
                $_SESSION['time'] = time(); 
                $_SESSION['mySession'] = $username;
                header('Location: user_auth.php');
            } else 
                $_SESSION['msg_result'] = $retVal;
        }
    } else {
        $_SESSION['msg_result'] = LOGIN_FAILED;
    }
} else {
    $_SESSION['msg_result'] = LOGIN_FAILED;
}
?>
