<?php
class Pay_Conf{

    /**
     * 支付宝配置
     */
    public static $alipay_config = array(
	'partner' => 2088911497000450,
	'private_key_path' => '/home/www/app/openapi/application/data/rsa_private_key.pem',
	'ali_public_key_path' => '/home/www/app/openapi/application/data/alipay_public_key.pem',
	'sign_type' => 'RSA',
	'input_charset' => 'utf-8',
	'cacert' => '/home/www/app/openapi/application/data/cacert.pem',
	'transport' => 'http' 
    );
    
    /**
     * 微信支付配置
     */    
    public static $wxpay_config = array(
	'appid' => 'wx761c6623989a48d9',
	'mch_id' => '1360817902',
	'web_appid' => 'wx9d323ac23a134fb3',
	'web_mchid' => '1387347202',
	'api_key' => 'MoneyMoneyComOn1104QuicklyBaby06' 	
    );

}
