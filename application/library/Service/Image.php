<?php 
class Service_Image{

	function __construct(){

	}

	public function picToStuhui($pic){ //单个图片地址转换
		$path = self::images_download($pic);
		$pickey = self::images_upload($path);
		$urlarr = explode("|",trim($pickey,"|"));
		$picurl = "http://img.stuhui.com/".$urlarr[0];
		return $picurl;
	} 

	public function images_download($icon_url){//远程图片下载
		$save_path = "/tmp/".date("Ymdhis").'.jpg';//这里生产的文件占用过多时会被清理
		$content = file_get_contents($icon_url);
		file_put_contents($save_path, $content);
		return realpath($save_path) ;
	}   

	public function images_upload($icon_path){//图片上传
		$superid = Common_Const::SUPER_USERID;//黑白咩userid
		$sessionid = Common_Util::GenerateSessionID($superid);
		$upload_url = Common_Const::API_URL.'/openapi/object?act=PostObject&type=pic&clientid=19bce6797da17a21afe3c21243cf331a2e203e60&os=ios&version=2.2.0&sessionid='.urlencode($sessionid);  
		$param = array('pic'=>'@'.$icon_path);
		$ret = self::request_post($upload_url,$param);
		return $ret['data']['object_key'];
	}   

	public function request_post($url,$data=null){  //执行上传

		if(isset($output)){
			unset($output);
		}   
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(!empty($data)){
			curl_setopt($curl,CURLOPT_POST,1);
			curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
		}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);
		curl_close($curl);
		return json_decode($output,true);
	}

}

?>
