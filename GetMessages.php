<?php
if(isset($_GET['chat_id'])){
    echo ChatService::GetMessages($_GET['chat_id']);
}
else{
    echo "an Error occured";
    http_response_code(400);
}
?>
