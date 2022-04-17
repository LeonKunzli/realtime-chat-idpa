<?php
require_once "LoginService.php";
if(isset($_POST['email'])){
    LoginService::LogOff(null);
}
?>
