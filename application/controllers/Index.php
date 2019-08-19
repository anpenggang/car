<?php 
class IndexController extends BaseController{

	private $userinfo = array();

	public function init() {
		
		$this->userinfo = Common_Const::$cdp_user_info;

	}
	
	public function loginAction() {
		header('content-type:text/html;charset=utf-8');

		if($this->getRequest()->getMethod() == 'POST') { 
			$username = Common_Util::getHttpReqQuery($this,'username','Str','n');
			$password = Common_Util::getHttpReqQuery($this,'password','Str','n');
			if(!empty($this->userinfo[$username]) && $this->userinfo[$username] == $password) { 
				Yaf_Session::getInstance()->set("username",$username);
				Yaf_Session::getInstance()->set('password',$password);
				$this->redirect("/countdownparty/subscribe/plain");
			} else {
				$this->getView()->assign("errmsg","用户名或者密码错误");
			}
		}

	}

	
	public function indexAction(){
		
		//header("content-type:text/html;charset=utf-8");
		//Yaf_Dispatcher::getInstance()->disableView();
		$username = Yaf_Session::getInstance()->get("username");
		if($username == NULL) {
			$this->redirect("/index/login");
			return false;
		}

	}

	public function logoutAction() {

		 Yaf_Session::getInstance()->del('username');
         Yaf_Session::getInstance()->del('user_uuid');
         Yaf_Session::getInstance()->del('order_serial');
         header('Location:/');
		 return false;
	}

	public function testAction() {
		Yaf_Dispatcher::getInstance()->disableView();
		//$this->getView()->assign("name","value");
		//ini_set("error_reporting",E_ALL);
		//$icon_path = 'http://2.heibaixiaoyuan.com/public/images/20171221cdp.jpg';
		//$this->images_upload($icon_path);
	}

	public function images_upload($icon_path){//图片上传
		//$superid = CommonConst::MIS_SUPER_USERID;//黑白咩userid
		//$sessionid = CommonUtil::GenerateSessionID($superid);
		$sessionid = '1559056817_76b653e986347d900f66_3288#tzsiym';
		//超级sessionid : 1559056817_76b653e986347d900f66_3288#tzsiym
		$upload_url = 'http://img.stuhui.com/openapi/object?act=PostObject&type=pic&clientid=19bce6797da17a21afe3c21243cf331a2e203e60&os=io
s&version=2.2.0&sessionid='.urlencode($sessionid);
		$param = array('pic'=>'@'.$icon_path);
		$ret = Common_Util::RequestHttpArray('POST',$upload_url,$param);
		var_dump($ret);
        //return $ret['data']['object_key'];
    } 

    public function qrcodeAction() {


        // usage : php qr.php yoururl > filename.png
        include_once APPLICATION_PATH."/application/third/phpqrcode/phpqrcode.php";//引入PHP QR库文件
        $url = "https://www.baidu.com";
        $errorCorrectionLevel = "H"; // H
        $matrixPointSize = "10"; // 10
        QRcode::png($url, false, $errorCorrectionLevel, $matrixPointSize);

    }

}//class
