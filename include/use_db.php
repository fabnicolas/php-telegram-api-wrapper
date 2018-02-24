<?php
$config = require(__DIR__."/../include/config.php");
require_once(__DIR__."/../include/functions.php");
require_once(__DIR__."/../lib/db.class.php");
$connection_params = array(
    'host' => $config['db_host'],
    'dbname' => $config['db_name']
);
$admin_params = array(
    'username' => $config['db_user'],
    'password' => $config['db_password']
);
$db = new DB($connection_params, $admin_params);
return $db;
?>