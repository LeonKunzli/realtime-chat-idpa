<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require_once 'Exception.php';
require_once 'PHPMailer.php';
require_once 'SMTP.php';
require_once 'LoginService.php';
require_once __DIR__ . '/config.php';
class ChatService {
    static function GetMessages($chat_id){
        if(ChatService::isUserInChat(LoginService::AuthorizeToken(null, null), $chat_id)) {
            $db = new Connect;
            $messages = array();
            $data = $db->prepare('SELECT send_time, content, u.username FROM message INNER JOIN chatuser u ON message.user_id=u.user_id WHERE message.chat_id = :chat_id');
            $data->execute([
                ':chat_id' => $chat_id
            ]);
            while ($OutputData = $data->fetch(PDO::FETCH_ASSOC)) {
                $users[$OutputData['message_id']] = array(
                    'send_time' => $OutputData['send_time'],
                    'content' => $OutputData['content'],
                    'username' => $OutputData['u.username'],
                );
            }
            return json_encode($messages);
        }
        else{
            'You are not in the requested Chat';
            http_response_code(401);
            exit;
        }
    }

    static function isUserInChat($user_id, $chat_id){
        LoginService::AuthorizeToken(null, null);
        $db = new Connect;
        $data = $db->prepare('SELECT user_chat_id FROM user_chat WHERE user_chat.chat_id = :chat_id AND user_chat.user_id = :user_id');
        $data->execute([
            ':chat_id' => $chat_id,
            ':user_id' => $user_id
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        if($OutputData=false){
            return false;
        }
        else{
            return true;
        }
    }

    static function NewMessage($content, $chat_id){
        $user_id = LoginService::AuthorizeToken(null, null);
        if(ChatService::isUserInChat($user_id, $chat_id) && !self::isChatFinished($chat_id)) {
            $db = new Connect;
            $data = $db->prepare('INSERT INTO message(messageUUID, content, user_id, chat_id) VALUES(:messageUUID, :content, :user_id, :chat_id)');
            $data->execute([
                ':messageUUID' => uniqid("m_"),
                ':content' => $content,
                ':user_id' => $user_id,
                ':chat_id' => $chat_id
            ]);
            return ChatService::GetMessages($chat_id);
        }
        else{
            echo 'You are not in the requested Chat';
            http_response_code(401);
            exit;
        }
    }

    static function isChatFinished($chat_id){
        $db = new Connect;
        $data = $db->prepare('SELECT chat_status FROM chat WHERE chat_id = :chat_id;');
        $data->execute([
            ':chat_id' => $chat_id
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        if($OutputData['chat_status']==2){
            return true;
        }
        else{
            return false;
        }
    }

    static function endChat($chat_id){
        //update chatstatus to be finished
        $db = new Connect;
        $data = $db->prepare('UPDATE chat SET chat_status = 2 WHERE chat_id = :chat_id');
        $data->execute([
            ':chat_id' => $chat_id
        ]);
    }

    static function createChat($emailOfCustomer){
        $db = new Connect;
        //create the chat
        $chatUUID = uniqid("c_");
        $data = $db->prepare('INSERT INTO chat(chatUUID) VALUES(:chatUUID)');
        $data->execute([
            ':chatUUID' => $chatUUID
        ]);
        $chat_id = self::getChatFromUUID($chatUUID);
        //Create user_chats for both users
        //TODO: fix incorrect integer value thing
        $data = $db->prepare('INSERT INTO user_chat(user_id, chat_id) VALUES((SELECT user_id FROM chatuser WHERE email = :email), :chat_id)');
        $data->execute([
            ':chat_id' => $chat_id,
            ':email' => $emailOfCustomer
        ]);
        $data = $db->prepare('INSERT INTO user_chat(user_id, chat_id) VALUES(:techSupport_id, :chat_id)');
        $data->execute([
            ':chat_id' => $chat_id,
            ':techSupport_id' => self::getAvailableTechSupport()
        ]);
        return $chat_id;
    }

    static function getChatFromUUID($chatUUID){
        $db = new Connect();
        $data = $db->prepare('SELECT chat_id FROM chat WHERE chatUUID = :chatUUID;');
        $data->execute([
            ':chatUUID' => $chatUUID
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        return $OutputData["chat_id"];
    }

    static function getAvailableTechSupport(){
        $db = new Connect;
        $data = $db->prepare('SELECT * FROM chatuser u WHERE u.status_id = 1 AND u.role_id = 1 AND (SELECT COUNT(*) FROM (SELECT uc.user_id FROM user_chat uc INNER JOIN chat c ON uc.chat_id = c.chat_id WHERE c.status_id = 1) AS subquery)<5 ORDER BY u.user_id LIMIT 1;
');
        $data->execute();
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        if(!is_int($OutputData["user_id"])){
            echo 'no available tech support was found';
            http_response_code(503);
            exit;
        }
        else{
            return $OutputData["user_id"];
        }
    }

    static function sendEmail($chat_id){
        $user_id = LoginService::AuthorizeToken(null, null);
        $email = LoginService::getEmailFromUser();
        if(ChatService::isUserInChat($user_id, $chat_id)) {
            $msg = "Hier ist Ihr Chat: \n";
            $db = new Connect;
            $data = $db->prepare('SELECT u.username, content FROM message INNER JOIN chatuser u ON message.user_id = u.user_id WHERE message.chat_id = :chat_id;');
            $data->execute([
                ':chat_id' => $chat_id
            ]);
            while ($OutputData = $data->fetch(PDO::FETCH_ASSOC)) {
                $msg .= $OutputData['username'] . ': ' . $OutputData['content'] . "\n";
            }
            // use wordwrap() if lines are longer than 70 characters
            $msg = wordwrap($msg, 70);
            // send email
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

            //Send mail using gmail
            $mail->IsSMTP(); // telling the class to use SMTP
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->SMTPSecure = "ssl"; // sets the prefix to the servier
            $mail->Host = "smtp.gmail.com"; // sets GMAIL as the SMTP server
            $mail->Port = 465; // set the SMTP port for the GMAIL server
            $mail->Username = "noreply.realtimechat@gmail.com"; // GMAIL username
            $mail->Password = "b77ITm0d"; // GMAIL password

            //Typical mail data
            $mail->AddAddress($email);
            $mail->SetFrom("noreply.realtimechat@gmail.com", "realtimechat");
            $mail->Subject = "Chatverlauf von Realtimechat";
            $mail->Body = $msg;

            try {
                $mail->Send();
                echo "Success!";
            } catch (Exception $e) {
                //Something went bad
                echo "Fail - " . $mail->ErrorInfo;
            }
        }
    }
}

?>