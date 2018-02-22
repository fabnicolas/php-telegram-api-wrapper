<?php
function post_parameter($key){
	return isset($_POST[$key]) ? $_POST[$key] : null;
}

function execute_atomically($function_to_execute){
	if($lock=flock('./lockfile', LOCK_EX)){
		try{$function_to_execute();}
		catch(Exception $e){echo $e;}
		finally{flock($lock, LOCK_UN);}
	}
}

function debug_var($var){
	echo var_export($var,true);
}

function json_echo($json_object){
	header('Content-Type: application/json');
	echo json_encode($json_object);	
}
?>