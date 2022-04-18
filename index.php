<?php
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/config.php';
class API {
    function Select(){
        $db = new Connect;
        $users = array();
        $data = $db->prepare('SELECT * FROM chatuser ORDER BY user_id');
        $data->execute();
        while($OutputData = $data->fetch(PDO::FETCH_ASSOC)){
            $users[$OutputData['user_id']] = array(
                    'user_id' => $OutputData['user_id'],
                    'username' => $OutputData['username'],
                    'email' => $OutputData['email'],
                    'password' => $OutputData['password'],
                    'role_id' => $OutputData['role_id']
            );
        }
        return json_encode($users);
    }
}
?>