<?php
header("Access-Control-Allow-Origin: *");
require_once "LoginService.php";
if(isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password'])){
    LoginService::Register($_POST['email'], $_POST['username'], $_POST['password'], 2);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>
