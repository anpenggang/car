<?php
/**
 **  微信服务号支付
 **/
class Service_Weixin {
	private $appId = 'wx9d323ac23a134fb3';
	private $appSecret = '53b1f1ce85ca538a771d68a2f809ed2b';

	private $authorizeUrl = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
	private $accessUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
	private $uinfoUrl = 'https://api.weixin.qq.com/sns/userinfo?lang=zh_CN';

	public function __construct($appid='',$appsecret=''){

		if(!empty($appid) && !empty($appsecret)){

			$this->appId = $appid ;
			$this->appSecret = $appsecret;
		}
	}

	public function redirectAccessUrl($redirectUrl){

		$param = 'appid='.$this->appId;
		$param .= '&redirect_uri='.$redirectUrl;
		$param .= '&response_type=code';
		$param .= '&scope=snsapi_userinfo';
		$param .= '&state=123#wechat_redirect';
		return $this->authorizeUrl.$param;
	}    


	public function accessTokenGet($code){

		$param = 'appid='.$this->appId;
		$param .= '&secret='.$this->appSecret;
		$param .= '&code='.$code;
		$param .= '&grant_type=authorization_code';

		$url = $this->accessUrl.$param;
		$ret = Service_Util::Request('get',$url,'');
		return json_decode($ret,true);
	}


	public function userinfoGet($tokens){

		$url = $this->uinfoUrl;
		$url .= '&access_token='.$tokens['access_token'];
		$url .=	'&openid='.$tokens['openid'];
		$ret = Service_Util::Request('get',$url,'');
		return json_decode($ret,true);
	}

	public function TokenGet($appid='' ,$appsecret=''){

		if(empty($appid) || empty($appsecret)){
			$appid = $this->appId;
			$appsecret = $this->appSecret;
		}	
		$tokenLink = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential';
		$tokenLink .='&appid='.$appid.'&secret='.$appsecret;
		$ret = Service_Util::Request('get',$tokenLink,'');
		return json_decode($ret,true);
	}


	public function QrcodeGet($token,$sceneid,$tmp=0){

		$link = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token ;
		$action = array(
			'scene'=>array('scene_id'=>$sceneid),
		);
		if($tmp == 1){
			$data = array('action_name'=>'QR_LIMIT_SCENE','action_info'=>$action);
		}else{
			$data = array('action_name'=>'QR_SCENE','expire_seconds'=>2592000,'action_info'=>$action);
		}
		$ret = Service_Util::Request('post',$link, json_encode($data));
		return json_decode($ret,true);
	}

}

