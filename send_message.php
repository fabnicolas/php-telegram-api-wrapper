<?php
$config = require("./include/config.php");
include("./include/functions.php");
include("./lib/telegrambot.class.php");

$success=false;

$telegram_id = post_parameter('chat_id');	// chat_id of the target user to send message to.
if(!empty($telegram_id) && is_numeric($telegram_id)){
	$message = post_parameter('message');	// text to send to the target user.
	if(!empty($message)){
		// Let's use telegram bot to send message to the target user.
		$telegram_bot = new TelegramBot($config['telegram_bot_API_key']);
		$telegram_bot->sendMessage($message, $telegram_id);
		$success=true;	// The operation was successful.
	}
}

if(!$success) {echo json(array('status'=>1, 'message'=>'Error on backend.')); http_response_code(400);}
else		  {echo json(array('status'=>0, 'message'=>'Message sent.')); http_response_code(200);}
?>
