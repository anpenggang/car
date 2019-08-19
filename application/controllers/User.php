<?php

/**
 *
 * @name UserController
 * @desc 用户控制器 
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version User.php v0.0 2017/9/30 新建 
 * @version User.php v1.0 2017/10/1 修改 [1. 添加增加用户方法。 2.添加修改用户方法]
 */
class UserController extends BaseController {
	
	private $_model = null; //用户表model

	/**
	 * 初始化方法 控制器被调用的时候先调用初始化方法 
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化UserModel
		$this->_model = new UserModel();
		
	}

	/**
	 * 登录方法 检测登录状态 以及判断是否需要登录，登录过程
	 * (1) 生成一个随机数（官方把他叫做3rd_session）
	 *（2）把这个随机数当session的key，session_key + openid为value。
	 * 即：session[3rd_session]=session_key+openid  
	 */
	public function loginAction() {

		//获取小程序传递过来的参数
		$code = Common_Util::getHttpReqQuery($this,'code','Str','n',''); //code码，用于去微信官方换取openid和session_key
		$rawData = Common_Util::getHttpReqQuery($this,'rawData','Str','n',''); //rawData
		$signature = Common_Util::getHttpReqQuery($this,'signature','Str','n','');//signature
		$encryptedData = Common_Util::getHttpReqQuery($this,'encryptedData','Str','n','');//encrytedData
		$iv = Common_Util::getHttpReqQuery($this,'iv','Str','n',''); //iv
		//根据code获取用户的openid，作为用户在系统里面的唯一标识
		$url = "https://api.weixin.qq.com/sns/jscode2session";
		$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		$post_data = [ 
			'grant_type' => 'authorization_code',
			'appid' => $config->wx->appid,
			'secret' => $config->wx->appsecret,
			'js_code' => $code
		];  
		$wxret = json_decode(Common_Util::RequestHttpArray('post',$url,$post_data),true);
		if (isset($wxret['openid'])) {//如果获取到openID则进行下一步处理，返回错误信息
			$result = $this->_model->getUserByOpenid($wxret['openid']);
			if (!empty($result)) {//用户表中有用户信息
				$rawdata = json_decode(html_entity_decode($rawData),true);

				//判断其头像和昵称有无修改有的话更新数据表,其余业务交给更新方法完成
				if ($result[0]['nickname'] != $rawdata['nickName'] || $result[0]['avatarUrl'] != $rawdata['avatarUrl']) {

					$this->updateUserInfo($wxret['openid'],$rawdata);
					return false;
				}

				unset($result[0]['openid']);
				$result[0]['sessionid'] = Common_Util::GenerateSessionID($result[0]['id']);
				return Common_Util::returnJson('20001','登录成功',$result);

			}
			//用户表中没有用户信息则调用添加方法，剩下的业务逻辑交给添加去处理
			$this->addUserAction($wxret['openid'],$rawData);
			return false;
		}
		return Common_Util::returnJson('20002','请重新授权',$wxret);	

	}

	/**
	 * 如果用户昵称或者头像改变后，更新数据表中昵称或者头像的记录
	 */
	public function updateUserInfo($openid,$rawdata) {

		$data = [
			'nickname' => $rawdata['nickName'],
			'avatarUrl' => $rawdata['avatarUrl']
		];
		$result = $this->_model->updateUserInfo($openid,$data);
		if (!empty($result)) {
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
	public function addUserAction($openid,$rawData) {

		$rawData = json_decode(html_entity_decode($rawData),true);
		$param = [
			'nickname' => $rawData['nickName'],
			'gender' => $rawData['gender'],
			'avatarUrl' => $rawData['avatarUrl'],
			'create_time' => time(),
			'openid' => $openid
		];	
		$result = $this->_model->addUser($param); 
		if (is_numeric($result) && (0 < $result)) {
			$userinfo = $this->_model->getUserInfo($result);
			unset($userinfo[0]['openid']);
			$userinfo['0']['sessionid'] = Common_Util::GenerateSessionID($result);
			return Common_Util::returnJson('20001','登录成功',$userinfo);
			exit;
		}
		//echo json_encode($data,JSON_UNESCAPED_UNICODE);
		
	}

	/**
	 *	在某次活动中禁用用户 向屏蔽关联表中插入一条记录
	 */
	public function forbiddenActUserAction() {

		//参数处理
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n','');
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');
		$result = $this->_model->forbiddenActUser($user_id,$act_id);
		if (is_numeric($result) && (0 < $result)) {
			return Common_Util::returnJson('20004','屏蔽该用户成功');
		}
		return Common_Util::returnJson('20005','屏蔽该用户失败');

	}

	/**
	 * 获取access_token
	 */
	public function getAccessTokenAction() {
		
		//1.获取的access_token
        $config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
        $token_url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$config->wx->appid."&secret=".$config->wx->appsecret;
        $get_arr=array();
        $post_data = http_build_query($get_arr); //生成 URL-encode 之后的请求字符串 备用
        $options = [ 
            'http' => [
                'method' => 'get',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content'=> $post_data,
                'timeout'=> 15 * 60 //超时时间 (单位:s)
            ]   
        ];  
        $context = stream_context_create($options);//创建资源流上下文 
        $result = file_get_contents($token_url,false,$context);
        $token_arr=json_decode($result,true);
        $access_token = $token_arr['access_token'];
		var_dump($token_arr);
		echo "<hr>";
		//2.获取unionid
		$openid = "oTbPz0AiDWQN77CzI3pCzRUlfqAY";
        $unionid_url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
        $get_arr_unionid=array();
        $postdata_unionid = http_build_query($get_arr_unionid); //生成 URL-encode 之后的请求字符串 备用
        $options_unionid = [ 
            'http' => [
                'method' => 'get',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content'=> $postdata_unionid,
                'timeout'=> 15 * 60 //超时时间 (单位:s)
            ]   
        ];  
        $context_unionid = stream_context_create($options_unionid);//创建资源流上下文 
        $result_unionid = file_get_contents($unionid_url,false,$context_unionid);
        $unionid=json_decode($result_unionid,true);
        var_dump($result_unionid);


	}

}
