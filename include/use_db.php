<?php
require_once(__DIR__."/../include/config.php");
require_once(__DIR__."/../include/functions.php");
require_once(__DIR__."/../lib/db.class.php");
$connection_params = array(
    'host' => DB_HOST,
    'dbname' => DB_NAME
);
$admin_params = array(
    'username' => DB_USER,
    'password' => DB_PASSWORD
);
$db = new DB($connection_params, $admin_params);
return $db;
?>