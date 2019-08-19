<?php 

class Verify_Ip extends RedisBase{

    private $limit = 100;
    private $ip_key = 'ip_%s';

    public function getRealIp(){

	$realip = '0.0.0.0';
	if (isset($_SERVER)) {
	    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		foreach ($arr AS $ip) {
		    $ip = trim($ip);
		    if ($ip != 'unknown') {
			$realip = $ip; 
			break;
		    }
		}
	    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
		$realip = $_SERVER['HTTP_CLIENT_IP'];
	    } else {
		if (isset($_SERVER['REMOTE_ADDR'])) {
		    $realip = $_SERVER['REMOTE_ADDR'];
		}
	    }
	} else {
	    if (getenv('HTTP_X_FORWARDED_FOR')) {
		$realip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif (getenv('HTTP_CLIENT_IP')) {
		$realip = getenv('HTTP_CLIENT_IP');
	    } else {
		$realip = getenv('REMOTE_ADDR');
	    }
	}
	preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
	$realip = !empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
	return $realip;
    }


    public function ipSmsIncr(){
	
	$ip = $this->getRealIp();
	$key = sprintf($this->ip_key,$ip);
	$ipCache = new Rediska_Key($key);
	if($ipCache->isExists()){
	    $ipnum = $ipCache->increment(1);
	}else{
	    $ipnum = 1;
	    $ipCache->setValue($ipnum);
	}
	$expiretime = strtotime(date('Y-m-d',time()))+86400-time();
	$ipCache->expire($expiretime);
	return $ipnum;
    }


    public function isIpAble(){
	
	$ipnum = $this->ipSmsIncr();
	if($ipnum >= $this->limit){
	    return false;
	}
	return true;
    }
}

?>
