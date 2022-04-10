<?php
require_once __DIR__ . '/config.php';
require "chatservice.php";
class LoginService
{
    function LogIn($email, $password){
        //check if is correct in db
        $db = new Connect;
        $data = $db->prepare('SELECT * FROM chatuser WHERE email = :email AND password = :password');
        $data->execute([
            ':email' => $email,
            ':password' => $password
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        //put token into database with expiry date
        $this->createToken($OutputData["user_id"]);
        return json_encode($OutputData);
    }

    static function AuthorizeToken(){
        //see if token was created more than 30 mins ago
        $token = $_SESSION["token"];
        $db = new Connect;
        $data = $db->prepare('SELECT creation_timestamp, user_id FROM token WHERE unique_id = :unique_id AND creation_timestamp BETWEEN NOW() - INTERVAL 30 MINUTE AND NOW()');
        $data->execute([
            ':unique_id' => $token
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        if($OutputData==false){
            exit();
        }
        return $OutputData["user_id"];
    }

    function LogOff(){
        session_destroy();
    }

    function Register($email, $username, $password, $role_id){
        $db = new Connect;
        if($this->IsEmailUnique($email)) {
            $data = $db->prepare('INSERT INTO chatuser(email, username, password, role_id) VALUES(:email, :username, :password, :role_id)');
            $data->execute([
                ':email' => $email,
                ':username' => $username,
                ':password' => $password,
                ':role_id' => $role_id
            ]);
        }
        else{
            //TODO: if email is not unique
            exit;
        }
    }

    function IsEmailUnique($email){
        $db = new Connect;
        $data = $db->prepare('SELECT COUNT(user_id) FROM chatuser WHERE email = :email');
        $data->execute([
            ':email' => $email
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        if($OutputData["COUNT(user_id)"]!=0){
            return false;
        }
        if(!(filter_var($email, FILTER_VALIDATE_EMAIL))){
            return false;
        }
        return true;
    }

    function CreateToken($user_id){
        $token = uniqid("t_");
        session_start();
        $_SESSION["token"] = $token;
        $db = new Connect;
        $data = $db->prepare('INSERT INTO token(unique_id, user_id) VALUES(:unique_id, :user_id)');
        $data->execute([
            ':unique_id' => $token,
            ':user_id' => $user_id
        ]);
    }
}
$LoginService = new LoginService();
$ChatService = new ChatService();
echo $LoginService->LogIn("kuenzlil@bzz.ch", "1234");
echo $ChatService->NewMessage("Hello World", 6, 2);
echo $ChatService->GetMessages(2);
$ChatService->sendEmail("kuenzlil@bzz.ch", 2, "realtimechat");