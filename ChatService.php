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
        LoginService::AuthorizeToken();
        if(ChatService::isUserInChat(LoginService::AuthorizeToken(), $chat_id)) {
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
            exit;
        }
    }

    static function isUserInChat($user_id, $chat_id){
        LoginService::AuthorizeToken();
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
        $user_id = LoginService::AuthorizeToken();
        if(ChatService::isUserInChat($user_id, $chat_id)) {
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
            exit;
        }
    }

    static function getEmailFromUser(){
        $user_id = LoginService::AuthorizeToken();
        $db = new Connect;
        $data = $db->prepare('SELECT email FROM chatuser WHERE user_id = :user_id;');
        $data->execute([
            ':chat_id' => $user_id
        ]);
        return $data->fetch(PDO::FETCH_ASSOC);
    }

    static function createChat(){

    }

    static function sendEmail($chat_id){
        $user_id = LoginService::AuthorizeToken();
        $email = ChatService::getEmailFromUser();
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