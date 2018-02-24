<?php
$db = include_once(__DIR__."/include/use_db.php");

$db->pdo->query("CREATE TABLE IF NOT EXISTS `updates` (
    `update_id` bigint(20) NOT NULL,
    `message_id` bigint(20) NOT NULL,
    `from_id` bigint(20) NOT NULL,
    `from_username` varchar(255) NOT NULL,
    `date` bigint(20) NOT NULL,
    `text` text NOT NULL,
    PRIMARY KEY (`update_id`,`message_id`)
  )");

unlink(__FILE__);
?>