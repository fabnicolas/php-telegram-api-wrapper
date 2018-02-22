<?php
class TelegramBot{
	var $base_bot_url;
	var $token=null;
	
	function __construct($token){
		$this->token=$token;
		$this->base_bot_url="https://api.telegram.org/bot".$token."/";
	}
	
	private function callTelegramAPI($command, $data){
		return file_get_contents(($this->base_bot_url).$command."?".http_build_query($data));
	}
	
	function sendMessage($text, $chat_id){
		$this->callTelegramAPI("sendMessage",['text'=>$text,'chat_id'=>$chat_id]);
	}
	
	function sendPhoto($image_url, $chat_id, $callback=null){
		$url = ($this->base_bot_url)."sendPhoto?chat_id=".$chat_id;
		$post_fields = array('chat_id' => $chat_id, 'photo' => new CURLFile(realpath($image_url)));
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
		$output = curl_exec($ch);
		if($callback!=null) $callback();
	}
	
	function getUpdates($offset=0,$limit=100,$timeout=0){
		return $this->callTelegramAPI("getUpdates",['offset'=>$offset,'limit'=>$limit,'timeout'=>$timeout]);
	}
}
?>