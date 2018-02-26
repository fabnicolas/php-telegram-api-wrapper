<?php
$config = require("./include/config.php");
include("./include/functions.php");
include("./lib/telegrambot.class.php");
include("./lib/uploader.class.php");

$success=false;

$telegram_id = post_parameter('chat_id');	// chat_id of the target user to send photo to.
if(!empty($telegram_id) && is_numeric($telegram_id)){
	// Let's instantiate an uploader to upload our image, sent in payload, stored in $_FILES['image'].
	$uploader = new Uploader("./tmp/");
	if(!empty($_FILES['image'])){
		if(($file=($uploader->uploadImage($_FILES['image'])))!=false){
			// If the image was uploaded successfully to our server, let's send it to the target.
			$telegram_bot = new TelegramBot($config['telegram_bot_API_key']);
			$telegram_bot->sendPhoto(($uploader->getDirectory()).$file, $telegram_id, function() use ($uploader, $file){
				// When communication is finished, remove the image from the server.
				$uploader->destroyImage($file);
			});
			$success=true;	// The operation was successful.
		}
	}
}

if(!$success) {echo json(array('status'=>1, 'message'=>'Error on backend.')); http_response_code(400);}
else		  {echo json(array('status'=>0, 'message'=>'Photo sent.')); http_response_code(200);}
?>
