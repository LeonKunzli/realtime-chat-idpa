<?php
require_once __DIR__ . '/config.php';
class API {
    function Select(){
        $db = new Connect;
        $data = $db->prepare('SHOW TABLES');
        $data->execute();
        $OutputData = $data->fetch(PDO::FETCH_ASSOC);
        return json_encode($OutputData);
    }
}

$API = new API;
header('Content-Type: application/json');
echo $API->Select();
?>