<?php
class TelegramBot{
	var $base_bot_url;
	var $token=null;
	var $ca_cert=null;
	
	function __construct($token){
		$this->token=$token;
		$this->base_bot_url="https://api.telegram.org/bot".$token."/";
	}

	function useCertificate($path){
		$this->ca_cert=$path;
	}

	private function httpRequest($url){
		if($this->ca_cert){
			$ssl_options = array("ssl"=>array(
				"cafile" => ($this->ca_cert),
				"verify_peer"=> true,
				"verify_peer_name"=> true,
			));
			return file_get_contents($url, false, stream_context_create($ssl_options));
		}else{
			return $this->url_get_contents($url);
		}
	}

	function url_get_contents($url){
		if(!function_exists('curl_init')){ 
			return file_get_contents($url);
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	private function callTelegramAPI($command, $data){
		return $this->httpRequest(($this->base_bot_url).$command."?".str_replace("&amp;", "&", http_build_query($data)));
	}
	
	function sendMessage($text, $chat_id, $disable_notifications=false){
		$this->callTelegramAPI("sendMessage",[
			'text'=>$text,
			'chat_id'=>$chat_id,
			'disable_notification'=>$disable_notifications
		]);
	}
	
	function sendPhoto($image_url, $chat_id, $caption='', $callback=null){
		$url = ($this->base_bot_url)."sendPhoto?chat_id=".$chat_id;
		$post_fields = array(
			'chat_id' => $chat_id,
			'photo' => new CURLFile(realpath($image_url)),
			'caption' => $caption
		);
		$ch = curl_init(); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type:multipart/form-data"));
		curl_setopt($ch, CURLOPT_URL, $url); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
		$output = curl_exec($ch);
		curl_close($ch);
		if($callback!=null) $callback();
	}
	
	function getUpdates($offset=0,$limit=100,$timeout=0){
		return $this->callTelegramAPI("getUpdates",['offset'=>$offset,'limit'=>$limit,'timeout'=>$timeout]);
	}

	function parseUpdates($updates,$chat_id=null){
		$result = json_decode($updates);
		if($result->ok){
			if(array_key_exists('result', $result)){
				$updates=$result->result;
				$parsed_update=array();
				foreach($updates as $key=>$update){
					$message=$update->message;
	
					// Reconstruct user message in a more human readable way with only necessary info.
					$parsed_message=array();
					$parsed_message['update_id']=$update->update_id;
					$parsed_message['message_id']=$message->message_id;
					$parsed_message['from_id']=$message->from->id;
					$parsed_message['from_username']=isset($message->from->username) ? $message->from->username : '';
					$parsed_message['date']=$message->date;
					$parsed_message['text']=$message->text;

					// If we are parsing all messages or we are parsing only messages from a certain chat_id...
					if($chat_id==null || $chat_id==$parsed_message['from_id']){
						// Save the parsed message to a list.
						$parsed_update[]=$parsed_message;
					}
				}
				return $parsed_update;
			}else return false;
		}else return false;
	}

	function getParsedUpdates($chat_id=null,$offset=0,$limit=100,$timeout=0){
		return $this->parseUpdates($this->getUpdates($offset,$limit,$timeout),$chat_id);
	}
}
?>