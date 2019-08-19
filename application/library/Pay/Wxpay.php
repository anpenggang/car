<?php
class Pay_Wxpay{

    private $config; 

    public function __construct(){

	$this->config = Pay_Conf::$wxpay_config;
    }


    public function SignBuild($param) {
	$strA = '';
	ksort ( $param );
	foreach ( $param as $k => $v ) {
	    if (! empty ( $v ) && $k != 'sign' &&  $k !='act') {
		$strA .= $k.'='.$v.'&';
	    }
	}
	$strA .= "key=" . $this->config ['api_key'];
	return strtoupper ( md5 ( $strA ) );
    }

    public function XmlBuild($data) {
	$xml = "<xml>";
	foreach ( $data as $k => $v ) {
	    $xml .= "<" . $k . ">" . $v . "</" . $k . ">";
	}
	$xml .= "</xml>";
	return $xml;
    }

    public function HttpPost($url, $data) {

	$data = $this->XmlBuild ( $data );
	$curl = curl_init ();
	$header [] = "Content-type: text/xml"; 
	curl_setopt ( $curl, CURLOPT_URL, $url );
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ); 
	curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, false );
	curl_setopt ( $curl, CURLOPT_HEADER, 0 ); 
	curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 );
	curl_setopt ( $curl, CURLOPT_POST, true ); 
	curl_setopt ( $curl, CURLOPT_POSTFIELDS, $data );
	curl_setopt ( $curl, CURLOPT_TIMEOUT, 5 );
	$responseText = curl_exec ( $curl );
	if (curl_errno ( $curl )) {
	    echo 'Errno' . curl_error ( $curl );
	}
	curl_close ( $curl );
	return json_encode ( simplexml_load_string ( $responseText, 'SimpleXMLElement', LIBXML_NOCDATA ) );
    }


    public function UnifiedOrderAppParam($param){
	
	$data = array(
	    'appid' => $this->config ['appid'],
	    'mch_id' => $this->config ['mch_id'],
	    'nonce_str' => time (),
	    'body' => $param['body'],
	    'out_trade_no' => $param['orderid'],
	    'total_fee' => $param['price']*100,
	    'spbill_create_ip' => '192.168.1.109',
	    'notify_url' => $param['notify_url'],
	    'trade_type' => 'APP'    
	);
	ksort ( $data );
	$sign = $this->SignBuild ( $data );
	$data ['sign'] = $sign;
	return $data;
    }

    
    public function UnifiedOrderJsapiParam($param){
    
	$data = array(
	    'appid' => $this->config ['web_appid'],
	    'mch_id' => $this->config ['web_mchid'],
	    'nonce_str' => time (), 
	    'body' => $param['body'],
	    'out_trade_no' => $param['orderid'],
	    'total_fee' => $param['price']*100,
	    'spbill_create_ip' => '192.168.1.109',
	    'notify_url' => $param['notify_url'],
	    'trade_type' => 'JSAPI',
	    'openid'=>$param['openid'],   
	);  
	ksort ( $data );
	$sign = $this->SignBuild ( $data );
	$data ['sign'] = $sign;
	return $data;
    }


    public function UnifiedOrderApp($param) {

	$data = $this->UnifiedOrderAppParam($param);
	$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	$ret = $this->HttpPost ( $url, $data );
	$ret = json_decode ( $ret, true );
	if ($ret ['return_code'] == 'SUCCESS' && $ret ['result_code'] == 'SUCCESS') {
	    $time = time();
	    $app_sign = array(
		'appid'=>$this->config['appid'],
		'noncestr'=>$ret ['nonce_str'],
		'package'=>'Sign=WXPay',
		'partnerid'=>$data['mch_id'],
		'timestamp'=>$time,
		'prepayid'=>$ret['prepay_id'],
	    );
	    $str_app_sign = $this->SignBuild($app_sign);
	    $res = array (
		'return_code' => 0,'return_msg' => 'ok',
		'data' => array (
		    'prepayid' => $ret ['prepay_id'],
		    'sign' => $str_app_sign,
		    'nonce_str' => $ret ['nonce_str'],
		    'order' => $data,
		    'timestamp'=>$time, 
		    'appid' => $this->config ['appid'],
		    'orderid'=>$param['orderid'],
		) 
	    );
	    return $res;
	} else {
	    return array ('return_code' => 400001,'return_msg' => '提交失败',); 
	}
    }


    public function UnifiedOrderJsapi($param){
	
	$data = $this->UnifiedOrderJsapiParam($param);
	$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	$ret = $this->HttpPost ( $url, $data );
	$ret = json_decode ( $ret, true );
	if ($ret ['return_code'] == 'SUCCESS' && $ret ['result_code'] == 'SUCCESS') {
	    $time = time();
	    $app_sign = array (
		'appId' => $this->config ['web_appid'],
		'timeStamp' => "$time",
		'nonceStr' => $ret ['nonce_str'],
		'package' => 'prepay_id=' . $ret ['prepay_id'],
		'signType' => 'MD5' 
	    );
	    $str_app_sign = $this->SignBuild($app_sign);
	    $res = array (
		'return_code' => 0,'return_msg' => 'ok',
		'data' => array (
		    'prepayid' => $ret ['prepay_id'],
		    'sign' => $str_app_sign,
		    'nonce_str' => $ret ['nonce_str'],
		    //'order' => $data,
		    'timestamp'=>"$time", 
		    'appid' => $this->config ['web_appid'] 
		) 
	    );
	    return $res;
	} else {
	    return array ('return_code' => 400001,'return_msg' => '提交失败',); 
	}
    }

    //异步回调的验证
    public function NotifyVerify($param){
    
	if(isset($param['sign']) && !empty($param['sign'])){
	
	    ksort($param);
	    $sign = $this->SignBuild($param);
	    file_put_contents('/tmp/scoialwx.log','签名:'.$sign."\n",FILE_APPEND);
	    if($param['sign'] == $sign){
		return true;
	    }
	    return false;
	}
	return false;
    }

    public function NotifyReturn($param){
    
	$xml = "<xml>";
	foreach($param as $k=>$v){
	    if(is_numeric($v)){
		$xml.="<".$k.">".$v."</".$k.">";
	    }else{
		$xml.="<".$k."><![CDATA[".$v."]]></".$k.">";
	    }
	}
	$xml.="</xml>";
	return $xml;
    }
}
