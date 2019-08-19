<?php

/**
 *
 * @name UserController
 * @desc 用户控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version User.php v0.0 2018/8/2 新建
 */
class UserController extends BaseController {

	private $_model = null;
	
	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//初始化用户模型
        $this->_model = new WcUserModel();

	}
	
	/**
	 * 登录方法 
	 */
	public function loginAction() {

		//获取小程序传递过来的登录参数
		$code = Common_Util::getHttpReqQuery($this,'code','Str','n',''); //code码，用于去微信官方换取openid和session_key
		$rawData = Common_Util::getHttpReqQuery($this,'rawData','Str','n');//rawData
		//$signature = Common_Util::getHttpReqQuery($this,'signature','Str','n','');//signature
		//$encryptedData = Common_Util::getHttpReqQuery($this,'encryptedData','Str','n','');//encrytedData
		//$iv = Common_Util::getHttpReqQuery($this,'iv','Str','n',''); //iv

		//根据code获取用户的openid，作为用户在系统里面的唯一标识
		$url = "https://api.weixin.qq.com/sns/jscode2session";
		$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		$post_data = [ 
				'grant_type' => 'authorization_code',
				'appid' => $config->wishcard->appid,
				'secret' => $config->wishcard->appsecret,
				'js_code' => $code
			];  

		$wxret = json_decode(Common_Util::RequestHttpArray('post',$url,$post_data),true);
		if (isset($wxret['openid']));; {//如果获取到openID则进行下一步处理，返回错误信息
			$result = $this->_model->getUserByOpenid($wxret['openid']);
			if (!empty($result)) {//用户表中有用户信息
				//print_r($result);exit;
				$rawdata = json_decode(html_entity_decode($rawData),true);
				//判断其头像和昵称有无修改有的话更新数据表,其余业务交给更新方法完成
				if ($result['nickname'] != $rawdata['nickName'] || $result['avatarUrl'] != $rawdata['avatarUrl'] || $result['gender'] != $rawdata['gender']) {
					$this->updateUserInfo($wxret['openid'],$rawdata,$wxret);
					return false;
				}   
				unset($result['openid']);
				//登录成功之后写入session
				$result['sessionid'] = $this->writeSession($result['id'],$wxret);
				return Common_Util::returnJson('20001','登录成功',$result);
			}
			//用户表中没有用户信息则调用添加方法，剩下的业务逻辑交给添加去处理
			$this->addUser($wxret['openid'],$rawData,$wxret);
			return false;
		}
		return Common_Util::returnJson('20002','请重新授权',$wxret);

	}

	/**
	 * 如果用户昵称或者头像改变后，更新数据表中昵称或者头像的记录
	 */
	 private function updateUserInfo($openid,$rawdata,$wxret) {
		$data = [
				'nickname' => $rawdata['nickName'],
				'avatarUrl' => $rawdata['avatarUrl'],
				'gender' => $rawdata['gender']
			];
		$result = $this->_model->updateUserInfo($openid,$data);
		if (!empty($result)) {
			//登录成功之后写入session
			$result['sessionid'] = $this->writeSession($result['id'],$wxret);

			return Common_Util::returnJson('20001','登录成功',$result);
		}
		return Common_Util::returnJson('20002','请重新授权');
	}

	/**
	 * 添加用户方法
	 * @param Integer $openid 微信用户的唯一标识码
	 * @param String  $rawData 微信获取到的用户信息 
	 * @return JSON $user_info 返回添加完之后的用户信息
	 */
	private function addUser($openid,$rawData,$wxret) {
	
		$rawData = json_decode(html_entity_decode($rawData),true);
		$param = [
			'nickname' => $rawData['nickName'],
			'gender' => $rawData['gender'],
			'avatarUrl' => $rawData['avatarUrl'],
			'city' => $rawData['city'],
			'province' => $rawData['province'],
			'country' => $rawData['country'],
			'create_time' => time(),
			'openid' => $openid,
		];
		$result = $this->_model->addUser($param);
		if (is_numeric($result) && (0 < $result)) {
			$userinfo = $this->_model->getUserInfo($result);
			unset($userinfo['openid']);
			//登录成功之后写入session
			$userinfo['sessionid'] = $this->writeSession($userinfo['id'],$wxret);

			return Common_Util::returnJson('20001','登录成功',$userinfo);
			exit;
		}
		//echo json_encode($data,JSON_UNESCAPED_UNICODE);

	}

	/**
	 * 登录成功之后写入session
	 */
	public function writeSession($userid,$wxret) {

		$sessionid = $this->randomStrNum(26);
		$data = [
			'session_key' => $wxret['session_key'],
			'openid' => $wxret['openid'],
			'user_id' => $userid
		];
		$value = json_encode($data);
		$this->_redis->setex($sessionid,60*60*3,$value);
		return $sessionid;

    }

}
