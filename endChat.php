<?php
require_once "ChatService.php";
if(isset($_PUT['chat_id'])){
    ChatService::endChat($_PUT['chat_id']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>