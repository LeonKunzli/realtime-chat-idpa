<?php
require_once "ChatService.php";
if(isset($_POST['email'])){
    ChatService::createChat($_POST['email']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>