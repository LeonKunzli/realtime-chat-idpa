<?php
require_once "ChatService.php";
if(isset($_POST['content']) && isset($_POST['chat_id'])){
    ChatService::NewMessage($_POST['content'], $_POST['chat_id']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>
