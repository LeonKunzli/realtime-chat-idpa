<?php
require_once __DIR__ . './config.php';
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

    function AuthorizeToken(){
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
echo $LoginService->LogIn("kuenzlil@bzz.ch", "1234");