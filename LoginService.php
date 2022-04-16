<?php
require_once __DIR__ . '/config.php';
require_once "ChatService.php";
class LoginService
{
    static function LogIn($email, $password){
        //check if is correct in db
        $db = new Connect;
        $data = $db->prepare('SELECT * FROM chatuser WHERE email = :email AND password = :password');
        $data->execute([
            ':email' => $email,
            ':password' => $password
        ]);
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        //put token into database with expiry date
        if($OutputData = false){
            echo 'password and email do not match';
            http_response_code(401);
        }
        LoginService::createToken($OutputData["user_id"]);
        self::updateUserStatus(1);
        return json_encode($OutputData);
    }

    static function updateUserStatus($user, $status){
        if($user = null) {
            $user = self::AuthorizeToken();
        }
        $db = new Connect;
        $data = $db->prepare('UPDATE chatuser SET status_id = :status WHERE user_id = :user_id');
        $data->execute([
            ':status' => $status,
            ':user_id' => $user
        ]);
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
            echo 'Your Token has expired.';
            http_response_code(401);
            exit();
        }
        return $OutputData["user_id"];
    }

    static function LogOff($user){
        self::updateUserStatus($user, 2);
        session_destroy();
    }

    static function Register($email, $username, $password, $role_id){
        $db = new Connect;
        if(LoginService::IsEmailUnique($email)) {
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
            echo 'this email is already in use';
            http_response_code(401);
            exit;
        }
    }

    static function IsEmailUnique($email){
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

    static function getAllInactiveUsers(){
        //TODO: Get all users with either no session or expired token and log them off
    }

    static function CreateToken($user_id){
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