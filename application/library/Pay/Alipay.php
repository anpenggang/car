<?php
class Pay_Alipay{

    private $config; 

    public function __construct(){
    
	$this->config = Pay_Conf::$alipay_config;
    }

    public function UnifiedOrderApp(){
    
	$return ['errno'] = 0;
	$return ['errmsg'] = 'ok';
	$return ['data'] = array(
	    'orderid'=>$param['orderid'],
	    'totalprice'=>$param['price'],
	    'notify_url'=>$param['notify_url'],   
	    'body'=>$param['body'],
	);
	return $return;
    }
}
