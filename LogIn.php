<?php
header("Access-Control-Allow-Origin: *");
require_once "LoginService.php";
if(isset($_POST['email']) && isset($_POST['password'])){
    LoginService::LogIn($_POST['email'],$_POST['password']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>
