<?php
class Upload_Image {

	public $client ;
	public function __construct(){

		$this->client = Upload_Util::createClient(Upload_Conf::$endpoint);
	}

	public function upload($file,$size,$postfix){

		$bucket = Upload_Conf::$bucket;
		$image_support = Upload_Conf::$image_support;
		$mime = "application/octet-stream";
		if(!empty($postfix) && isset($image_support[$postfix])){
			$mime = $image_support[$postfix];
		}

		$pickey = md5( time() . rand(1000, 9999) . $bucket );
		$file_content = file_get_contents($file);
		$ret = Upload_Util::putResourceObject($this->client, $bucket, $pickey, $file_content, $size, $mime);
		if($ret !== false){
			return $pickey;
		}
		return false;
	}


	public function PostfixGet($name){

		$pos = strrpos($name, ".");
		$postfix = substr($name, $pos+1);
		return $postfix;
	}


	public function ImageUpload($file){

		$pics = '';
		foreach($file as $key){
			if(is_uploaded_file($key['tmp_name'])){
				$postfix = $this->PostfixGet($key['name']);
				$pickey = $this->upload($key['tmp_name'],$key['size'],$postfix);
				if(!empty($pickey)){
					$pics .= Upload_Conf::$image_domain.$pickey.'|';
				}
			}
		}
		$pics = trim($pics,'|');
		return $pics;
	}    
}
