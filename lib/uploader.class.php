<?php
class Uploader{
	var $upload_dir;
	
	function uuidv4(){
		return (implode('-', [bin2hex(random_bytes(4)),
			bin2hex(random_bytes(2)),
			bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
			bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
			bin2hex(random_bytes(6))
		]));
	}
	
	function __construct($upload_dir){
		$this->upload_dir=$upload_dir;
	}
	
	function getDirectory(){
		return $this->upload_dir;
	}
	
	function uploadImage($file){	// $_FILES['image']
		$verifyimg = getimagesize($file['tmp_name']);
		$mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file['tmp_name']);
		$supported_types = array('image/png', 'image/jpg', 'image/jpeg', 'image/gif');
		if(!in_array($verifyimg['mime'], $supported_types) || !in_array($mime, $supported_types)) return false;
		$upload_file = ($this->uuidv4()).".".(explode("/",$mime)[1]);
		if(move_uploaded_file($file['tmp_name'], (($this->getDirectory()).$upload_file))) return $upload_file;
		else return false;
	}
	
	function destroyImage($file){
		unlink(($this->getDirectory()).$file);
	}
}
?>