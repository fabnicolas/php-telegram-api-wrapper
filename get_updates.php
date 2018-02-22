<?php
require_once(__DIR__."/include/config.php");
require_once(__DIR__."/include/functions.php");
include_once(__DIR__."/lib/telegrambot.class.php");

$success=false;

$db = include_once(__DIR__."/include/use_db.php");

$telegram_id = post_parameter('chat_id');	// At moment ignored anyway
if(!empty($telegram_id) && is_numeric($telegram_id)){
	$gigi = new TelegramBot(APP_TELEGRAM_SECRET_TOKEN_STRING);
	$update_id = post_parameter('update_id');
	$update_id = $update_id!=null ? $update_id : 0;
	
	$result = json_decode($gigi->getUpdates($update_id));
	if($result->ok){
		if(array_key_exists('result', $result)){
			$updates=$result->result;
			$fetched_updates=array();
			foreach($updates as $key=>$update){
				$message=$update->message;

				// Query params
				$query_params=array();
				$query_params['update_id']=$update->update_id;
				$query_params['message_id']=$message->message_id;
				$query_params['from_id']=$message->from->id;
				$query_params['from_username']=$message->from->username;
				$query_params['date']=$message->date;
				$query_params['text']=$message->text;

				// Insert values into database
				$statement = $db->getPDO()->prepare(
					"INSERT INTO updates (update_id, message_id, from_id, from_username, date, text) 
					 VALUES (:update_id, :message_id, :from_id, :from_username, :date, :text)");
				$statement->execute($query_params);

				$fetched_updates[]=$query_params;
			}
			json_echo(array('status'=>0, 'message'=>$fetched_updates));
		}else{
			json_echo(array('status'=>1, 'message'=>'No updates pending.'));
		}
	}
	
	$success=true;
}

if(!$success) http_response_code(400);
else		  http_response_code(200);
?>
