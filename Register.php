<?php
require "LoginService.php";
if(isset($_POST['email']) && isset($_POST['username']) && isset($_POST['password']) && isset($_POST['role_id'])){
    LoginService::Register($_POST['email'], $_POST['username'], $_POST['password'], $_POST['role_id']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>