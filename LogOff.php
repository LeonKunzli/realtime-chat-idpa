<?php
header("Access-Control-Allow-Origin: *");
require_once "LoginService.php";
if(isset($_POST['email'])){
    LoginService::LogOff(null);
}
?>
