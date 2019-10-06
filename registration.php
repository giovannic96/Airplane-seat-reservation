<?php
require_once('utility.php');
session_start();
header('Location: signup.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['username']) && isset($_POST['password']) && isset($_POST['confirm_password']) && 
        !empty($_POST['username'] && !empty($_POST['password']) && !empty($_POST['confirm_password']))) {

        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        $isPasswordCorrect = checkPasswords($password, $confirm_password);
        $isEmailCorrect = checkEmail($username);

        if(!$isEmailCorrect)
            $_SESSION['msg_result'] = EMAIL_INCORRECT;
        else if(!$isPasswordCorrect)
            $_SESSION['msg_result'] = PASSWORD_INCORRECT;
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT); //it uses the bcrypt algorithm
            $username = mySanitizeString($username);
            $_SESSION['msg_result'] = trySignup($username, $hash);
            if($_SESSION['msg_result'] == REGISTER_OK)
                header('Location: login.php');
        }
    } else {
        $_SESSION['msg_result'] = REGISTER_FAILED;
    }
} else {
    $_SESSION['msg_result'] = REGISTER_FAILED;
}
?>
