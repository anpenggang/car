<?php 

class Verify_ImageCode extends RedisBase{

    private $imagecode_key = 'image_code_%s';

    /**
     * 获取随机验证码
     */
    public static function getRandCode($length=6){

	$code="0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";  
	$string="";  
	for($i=0;$i<$length;$i++) {  
	    $char=$code{rand(0,strlen($code)-1)};  
	    $string.=$char;  
	}  
	return $string;
    }


    private function getImageCode($mobile){

	$key = sprintf($this->imagecode_key,$mobile);
	$imgCache = new Rediska_Key($key);
	$info = $imgCache->getValue();
	$info = json_decode($info, true);
	if(empty($info)){
	    $info = array();
	}
	return $info ;
    }


    private function setImageCode($mobile,$code){

	$info = array('code'=>$code ,'time'=>time());
	$key = sprintf($this->imagecode_key,$mobile);
	$imgCache = new Rediska_Key($key);
	$info = $imgCache->setValue(json_encode($info));
    }

    public function showImageCode($mobile){

	$code = $this->getRandCode(6);
	$this->setImageCode($mobile,$code);
	$imageCodeObj = new Service_ImageCode($code ,6);
	$imageCodeObj->outputImageCode();
    }


    public function checkImageCode($mobile ,$code){
	
	$info = $this->getImageCode($mobile);
	if(!empty($info)){
	    $nowtime = time();
	    if($nowtime - $info['time'] >300 || strtolower($info['code']) != strtolower($code)){
		return false;
	    }
	    return true;
	}
	return false;
    }
}

?>
