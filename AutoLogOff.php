<?php
header("Access-Control-Allow-Origin: *");
require_once "LoginService.php";
//TODO:Call this once every x0 minutes
LoginService::autoLogOff();
?>