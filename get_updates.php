<?php
$config = require(__DIR__."/include/config.php");
require_once(__DIR__."/include/functions.php");
include_once(__DIR__."/lib/telegrambot.class.php");

$success=false;

$db = include_once(__DIR__."/include/use_db.php");

$telegram_bot = new TelegramBot($config['telegram_bot_API_key']);
$update_id = post_parameter('update_id');
$update_id = $update_id!=null ? $update_id : 0;

// We fetch and parse updates from Telegram API to store.
$result = $telegram_bot->getParsedUpdates(null,$update_id);
if($result){
	foreach($result as $key=>$message_params){
		// Insert all the updates (messages) inside the DB table for further reuses.
		$statement = $db->getPDO()->prepare(
			"INSERT INTO updates (update_id, message_id, from_id, from_username, date, text) 
			 VALUES (:update_id, :message_id, :from_id, :from_username, :date, :text)");
		$statement->execute($message_params);
	}
}

$telegram_id = post_parameter('chat_id');
if(!empty($telegram_id) && is_numeric($telegram_id)){
	$statement = $db->getPDO()->prepare("SELECT update_id,text FROM updates WHERE update_id >= :update_id
									AND from_id = :from_id ORDER BY update_id ASC");
	$statement->execute(array('update_id'=>$update_id, 'from_id'=>$telegram_id));
	$result = $statement->fetchAll();
	$parsed_result = array();
	$update_ids_todelete = array();
	foreach($result as $message){
		$current_update_id=$message['update_id'];
		$parsed_result[]=array('update_id'=>$current_update_id, 'text'=>$message['text']);
		$update_ids_todelete[]=$current_update_id;
	}
	$next_update_id=$update_id;
	if(!empty($update_ids_todelete)){
		$update_ids_string=$db->in_composer($update_ids_todelete);
		$statement = $db->getPDO()->prepare("DELETE FROM `updates` WHERE FIND_IN_SET(update_id, :update_id)");
		$statement->execute(array('update_id'=>$update_ids_string));
		$next_update_id=$update_ids_todelete[count($update_ids_todelete)-1]+1;
	}
	echo json(array('status'=>0, 'message'=>$parsed_result, 'next_update_id'=>$next_update_id));
	
	$success=true;
}

if(!$success) http_response_code(400);
else		  http_response_code(200);
?>
