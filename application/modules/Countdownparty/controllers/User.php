<?php

/**
 * 
 * @name UserController
 * @desc 用户控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version User.php v0.0 2017/12/18 新建
 **/

class UserController extends BaseController {

	private $_model = null;
	private $appid = null;
	private $secret = null;
    
    /** 
     * 初始化方法,控制器被调用的时候先执行初始化方法，可作用于当前控制器
     */
    public function init() {
    
		//调用父类的初始化方法
		parent::init();

		//实例化用户模型对象
		$this->_model = new CdpUserModel();

		//初始化 小程序账户信息
		$this->appid = 'wxe32566d8be4210c7';
		$this->secret = 'b4eaf60b02a1e220e15f6b5ac783d1c2';
		
    }   

	public function IndexAction() {

		echo 'usercontroller';

	}

	/**
	 * 小程序登录方法
	 *
	 * (1) 获取openid
	 * (2) 获取access_token
	 * (3) 根据openid和access_token获取unionid，作为用户在全局的唯一标识
	 */
	public function LoginAction() {

		if ($this->getRequest()->getMethod() == "POST") {

			//获取并处理小程序传递给后台的参数
			$code = Common_Util::getHttpReqQuery($this,'code','Str','n');//code码，用于去微信官方换取openid和session_key
			$raw_data = json_decode(html_entity_decode(Common_Util::getHttpReqQuery($this,'rawData','Str','n')),true);// rawData数据，微信用户开放信息
			// (1) 根据code获取用户的openid，用户获取下一步的unionid
			$openid_url = "https://api.weixin.qq.com/sns/jscode2session";
			//$openid_url = "https://api.weixin.qq.com/sns/jscode2session?appid={$this->appid}&secret={$this}&js_code=JSCODE&grant_type=authorization_code";
			$openid_post_data = [
				'grant_type' => 'authorization_code',
				'appid' => $this->appid,
				'secret' =>$this->secret,
				'js_code' => $code
			];
			$openid_ret = json_decode(Common_Util::RequestHttpArray('post',$openid_url,$openid_post_data),true);

			if (isset($openid_ret['openid'])) {//如果获取到openID则进行下一步操作
				//先去数据库查找有没此用户信息，有的话直接返回，否则获取进一步获取信息
				$user_info = $this->_model->getUserInfoByOpenid($openid_ret['openid']);
				if (!empty($user_info)) {

					//判断用户信息有无修改，有更新之其余业务逻辑交由更新方法完成，无返回之，
					if ($user_info['nickname'] != $raw_data['nickName'] 
						|| $user_info['avatarurl'] != $raw_data['avatarUrl'] 
						|| $user_info['city'] != $raw_data['city']) 
					{
						$this->updateUserInfo($user_info['id'],$raw_data);
						return false;
					}	
					if (!empty($user_info['unionid'])) {
						unset($user_info['unionid']);
					}
					unset($user_info['openid']);
					$user_info['sessionid'] = Common_Util::GenerateSessionID($user_info['id']);
					return Common_Util::returnJson('20001','登录成功',$user_info);
					return false;
				}
				//获取unionid成功,向用户表中新增数据
				$openid_ret['unionid'] = !empty($openid_ret['unionid']) ? $openid_ret['unionid'] : 0;
				$this->addUser($openid_ret['openid'],$openid_ret['unionid'],$raw_data);
				return false;

			}//endif isset(openid)	

			return Common_Util::returnJson('20002','登录失败，请重新授权');

		} else {

			return Common_Util::returnJson('10007','请求方法有误');		
	
		}
	}//endlogin
	
	/**
	 * 新增用户方法 
	 *
	 * @param string $openid openid.
	 * @param string $unionid unionid.
	 * @param array  $raw_data 包含用户信息的原始数组。
	 * @return string	成功返回用户信息json字符串，失败返回false。
	 */
	private function addUser($openid,$unionid,$raw_data) {

		$args = [
			'openid' => $openid,
			'unionid' => $unionid,
			'nickname' => $raw_data['nickName'],
			'gender' => $raw_data['gender'],
			'avatarurl' =>$raw_data['avatarUrl'],
			'create_time' => time(),
		];

		$result = $this->_model->addUser($args);
		if (is_numeric($result) && (0 < $result)) {

			$user_info = $this->_model->getUserInfoById($result);
			unset($user_info['openid']);
			unset($user_info['unionid']);
			$userinfo['sessionid'] = Common_Util::GenerateSessionID($result);
			return Common_Util::returnJson('20001','登录成功',$userinfo);

		}//endif

		return Common_Util::returnJson('20002','登录失败，请重新授权');	
	
	}

	/**
	 * 更新用户信息方法
	 *
	 * @param Int $user_id user_id.
	 * @param array $raw_data 包含用户信息的数组
	 * @return string 成功返回用户信息，失败返回错误信息
	 */
	private function updateUserInfo($user_id,$raw_data) {
		
		$args = [
			'nickname' => $raw_data['nickName'],
			'city' => $raw_data['city'],
			'avatarurl' => $raw_data['avatarUrl']
		];
		
		$user_info = $this->_model->updateUserInfo($user_id,$args);
		if (!empty($user_info)) {
			unset($user_info['openid']);
			unset($user_info['unionid']);
			$user_info['sessionid'] = Common_Util::GenerateSessionID($user_info['id']);
			return Common_Util::returnJson('20001','登录成功',$user_info);
		}

		return Common_Util::returnJson('20002','请重新授权');
	
	}

	/**
	 *
	 * 更新用户信息
	 */
	public function addUserInfoAction() {
		
		if($this->getRequest()->getMethod() == 'POST') {
			$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n');//用户id
			$dnickname = Common_Util::getHttpReqQuery($this,'dnickname','Str','n');//用户发送弹幕时的昵称
			$school = Common_Util::getHttpReqQuery($this,'school','Str','n');//用户学校
			$data = [
				'dnickname' => $dnickname,
				'school' => $school
			];
			$user_info = $this->_model->updateUserInfo($user_id,$data);
			if (!empty($user_info)) {	
				unset($user_info['openid']);
				unset($user_info['unionid']);
				return Common_Util::returnJson('20006','更新成功',$user_info);
			} else {
				return Common_Util::returnJson('20007','更新失败');
			}

		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}
		
	}
	
	/**
	 * 禁用用户
	 */
	public function forbiddenUserAction() {

		if ($this->getRequest()->getMethod() == "PUT") {

			//参数处理
			$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n','');

			$result = $this->_model->forbiddenUser($user_id);
			if ($result) {

				return Common_Util::returnJson('20004','屏蔽该用户成功');

			}

			return Common_Util::returnJson('20005','屏蔽该用户失败');

		} else {

			 return Common_Util::returnJson('10007','请求方法有误');		

		}
	}
	
	/**
	 * 添加管理员
	 */
	public function addManagerAction() {

		if ($this->getRequest()->getMethod() == 'PUT') {

			$my_id = Common_Util::getHttpReqQuery($this,'my_id','Int','n');
			$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n');
			$issupper_user = $this->_model->issupperUser($my_id);
			if (empty($issupper_user)) {
				return Common_Util::returnJson('20003','非管理员不能添加其他人为管理员');
			}
			$result = $this->_model->updateUserInfo($user_id,['issupper' => 6]);
			if ($result) {
				return Common_Util::returnJson('20006','更新成功');
			}
						
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 判断用户有没有关注公众号
	 */
	public function checkOASubscribeAction() {

		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n');
		$user_info = $this->_model->getUserInfoById($user_id);
		if(!empty($user_info)) {
			$user_oa_model = new CdpOAUserModel();
			$user_oa_info = $user_oa_model->getUserInfoByUnionid($user_info['unionid']);
			if(!empty($user_oa_info) && $user_oa_info['subscribe'] == 1) {
				return Common_Util::returnJson('20001','用户已关注公众号');
			}
			return Common_Util::returnJson('20002','用户还未关注公众号');
		}
		return Common_Util::returnJson('10004','用户未登录');
	}

}//UserClass
