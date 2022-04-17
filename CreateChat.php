<?php
require_once "ChatService.php";
if(isset($_POST['email'])){
    ChatService::createChat($_POST['email']);
    //echo 'This is not yet implemented';
    //http_response_code(503);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>