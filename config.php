<?php
class Connect extends PDO
{
    public function __construct()
    {
        $cleardb_url = parse_url("mysql://b78aec671d1949:d65f2295@eu-cdbr-west-02.cleardb.net/heroku_d8f911f74eab1dc?reconnect=true");
        $cleardb_server = $cleardb_url["host"];
        $cleardb_username = $cleardb_url["user"];
        $cleardb_password = $cleardb_url["pass"];
        $cleardb_db = substr($cleardb_url["path"],1);
        parent::__construct("mysql:host=$cleardb_server;dbname=$cleardb_db;port=3306", $cleardb_username, $cleardb_password,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

}
?>
