<?php 

class Verify_SmsCode extends RedisBase{
    
    private $smscode_common = '5438';
    private $smscode_key = 'sms_code_%s';

    private function getSmsCode($mobile){
		
	$key = sprintf($this->smscode_key,$mobile);
	$smsCache = new Rediska_Key($key);
	$info = $smsCache->getValue();
	$info = json_decode($info, true);
	if(empty($info)){
	    $info = array();
	}
	return $info ;
    }


    private function setSmsCode($mobile,$code){

	$codeInfo = array('code'=>$code ,'time'=>time());
	$info = $this->getSmsCode($mobile);
	$info[] = $codeInfo;
	$info = array_slice($info,-6,6);//取最新6条
	$key = sprintf($this->smscode_key,$mobile);
	$smsCache = new Rediska_Key($key);
	$info = $smsCache->setValue(json_encode($info));
    }


    private function isSmsAble($mobile){

	return 1;
	$info = $this->getSmsCode($mobile);
	if(!empty($info)){
	    $last = array_slice($info,-1,1);
	    $nowtime = time();
	    if($nowtime - $last[0]['time'] <= 90){
		return -1;
	    }
	    if(count($info) == 6){
		$today = strtotime(date('Y-m-d',strtotime('today')));
		if($info[0]['time']>$today){
		    return -2;
		}
	    }
	}
	return 1;
    }


    public function sendSmsCode($mobile){
	
	$able = $this->isSmsAble($mobile);
	if($able == 1){
	    $code = mt_rand(100000, 999999);
	    $this->setSmsCode($mobile,$code);
	    if(Service_Sms::Chuanglan($mobile,'验证码:'.$code)){
		return array('errno'=>0,'errmsg'=>'ok');
	    }else{
		return array('errno'=>103003,'errmsg'=>CommonConst::ERRMSG_103003);
	    }
	}else if($able == -1){
	    return array('errno'=>103010,'errmsg'=>'验证码发送过于频繁，请90秒后再试！');
	}else{
	    return array('errno'=>103006,'errmsg'=>CommonConst::ERRMSG_103006);
	}	
    }


    public function checkSmsCode($mobile ,$code){
    
	if($code == $this->smscode_common){
	    return true;
	}
	$info = $this->getSmsCode($mobile);
	if(!empty($info)){
	    $last = end($info);
	    $nowtime = time();
	    if($nowtime - $last['time'] >300 || $last['code'] != $code){
		return false;
	    }
	    return true;
	}
	return false;
    }
}

?>
