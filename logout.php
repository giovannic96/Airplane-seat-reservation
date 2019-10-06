<?php
require_once('utility.php');

check_inactivity();
if(!isset($_SESSION)) 
    session_start();

if(userLoggedIn())
    myDestroySession();
header('Location: index.php');
?>