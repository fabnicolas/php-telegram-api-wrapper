<?php
/*
  THIS SCRIPT WILL SELF-DESTRUCT AFTER EXECUTION!

  The purpose of this script is clear: create table `updates` inside your RDBMS.
  
  Everyone loves auto-installers, right?
  (Make sure your DB data are VALID before executing this script.)
*/
$db = include_once(__DIR__."/include/use_db.php");

$db->pdo->query(
  "CREATE TABLE IF NOT EXISTS `updates` (
    `update_id` bigint(20) NOT NULL,
    `message_id` bigint(20) NOT NULL,
    `from_id` bigint(20) NOT NULL,
    `from_username` varchar(255) NOT NULL,
    `date` bigint(20) NOT NULL,
    `text` text NOT NULL,
    PRIMARY KEY (`update_id`,`message_id`)
    )");

class SelfDestroy{function __destruct(){unlink(__FILE__);}}
$installation_finished = new DeleteOnExit();
?>