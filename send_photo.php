<?php
$config = require("./include/config.php");
include("./include/functions.php");
include("./lib/telegrambot.class.php");
include("./lib/uploader.class.php");

$success=false;

$telegram_id = post_parameter('chat_id');
if(!empty($telegram_id) && is_numeric($telegram_id)){
	$uploader = new Uploader("./tmp/");
	if(!empty($_FILES['image'])){
		if(($file=($uploader->uploadImage($_FILES['image'])))!=false){
			$gigi = new TelegramBot($config['telegram_bot_API_key']);
			$gigi->sendPhoto(($uploader->getDirectory()).$file, $telegram_id, function() use ($uploader, $file){
				$uploader->destroyImage($file);
			});
			$success=true;
		}
	}
}

if(!$success) {echo json(array('status'=>1, message=>'Error on backend.')); http_response_code(400);}
else		  {echo json(array('status'=>0, message=>'Photo sent.')); http_response_code(200);}
?>
