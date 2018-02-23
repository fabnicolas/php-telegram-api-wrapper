<?php
require_once(__DIR__."/include/config.php");
require_once(__DIR__."/include/functions.php");
include_once(__DIR__."/lib/telegrambot.class.php");

$success=false;

$db = include_once(__DIR__."/include/use_db.php");

$telegram_bot = new TelegramBot(APP_TELEGRAM_SECRET_TOKEN_STRING);
$update_id = post_parameter('update_id');
$update_id = $update_id!=null ? $update_id : 0;
	
$result = $telegram_bot->getParsedUpdates();
if($result){
	foreach($result as $key=>$message_params){
		// Insert values into database
		$statement = $db->getPDO()->prepare(
			"INSERT INTO updates (update_id, message_id, from_id, from_username, date, text) 
			 VALUES (:update_id, :message_id, :from_id, :from_username, :date, :text)");
		$statement->execute($message_params);
	}
	json_echo(array('status'=>0, 'message'=>$result));	// At moment just for testing - Will be replaced.
}else{
	json_echo(array('status'=>1, 'message'=>'No updates pending.'));
}
	
$success=true;


if(!$success) http_response_code(400);
else		  http_response_code(200);
?>
