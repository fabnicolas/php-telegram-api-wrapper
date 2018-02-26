<?php
$config = require(__DIR__."/include/config.php");
require_once(__DIR__."/include/functions.php");
include_once(__DIR__."/lib/telegrambot.class.php");

$success=false;

// Let's include database and initialize our telegram bot.
$db = include_once(__DIR__."/include/use_db.php");
$telegram_bot = new TelegramBot($config['telegram_bot_API_key']);

// Parameter 'update_id'; 0 if null.
$update_id = post_parameter('update_id', 0);

// We fetch and parse updates from Telegram API to store inside database.
$result = $telegram_bot->getParsedUpdates(null,$update_id);
if($result){
	foreach($result as $key=>$message_params){
		// Insert all the updates (messages) inside the DB table for further reuses (row by row).
		$statement = $db->getPDO()->prepare(
			"INSERT INTO updates (update_id, message_id, from_id, from_username, date, text) 
			 VALUES (:update_id, :message_id, :from_id, :from_username, :date, :text)");
		$statement->execute($message_params);
	}
}
// Note: you can separate this script in a separate file as 'caching script' for a cron job.


$telegram_id = post_parameter('chat_id');	// chat_id of the target we want to analyze data from.
if(!empty($telegram_id) && is_numeric($telegram_id)){
	// Let's retrieve from RDBMS all new incoming messages.
	$statement = $db->getPDO()->prepare("SELECT update_id,text FROM updates WHERE update_id >= :update_id
									AND from_id = :from_id ORDER BY update_id ASC");
	$statement->execute(array('update_id'=>$update_id, 'from_id'=>$telegram_id));
	$result = $statement->fetchAll();

	// Let's parse those data and get IDs of the messages to mark as read (By deleting their records from database).
	$parsed_result = array();
	$update_ids_todelete = array();
	foreach($result as $message){
		$current_update_id=$message['update_id'];
		$parsed_result[]=array('update_id'=>$current_update_id, 'text'=>$message['text']);
		$update_ids_todelete[]=$current_update_id;
	}
	$next_update_id=$update_id;
	if(!empty($update_ids_todelete)){
		// Deleting procedure.
		$update_ids_string=$db->in_composer($update_ids_todelete);
		$statement = $db->getPDO()->prepare("DELETE FROM `updates` WHERE FIND_IN_SET(update_id, :update_id)");
		$statement->execute(array('update_id'=>$update_ids_string));
		$next_update_id=$update_ids_todelete[count($update_ids_todelete)-1]+1;
	}
	
	$success=true;
}

if(!$success) {echo json(array('status'=>1, 'message'=>'Error on backend.')); http_response_code(400);}
else		  {echo json(array('status'=>0, 'message'=>$parsed_result, 'next_update_id'=>$next_update_id)); http_response_code(200);}
?>
