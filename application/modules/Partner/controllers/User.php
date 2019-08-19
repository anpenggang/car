<?php

/**
 *
 * @name UserController
 * @desc 用户控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version User.php v0.0 2018/3/5 新建
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
        $this->_model = new PtnUserModel();

	}
	
	/**
	 * 首页方法
	 */
	public function indexAction() {

		return Common_Util::returnJson('20001','请求成功，返回首页信息');

	}
	
	/**
	 * 登录方法 
	 */
	public function loginAction() {

		//获取小程序传递过来的登录参数
		$code = Common_Util::getHttpReqQuery($this,'code','Str','n',''); //code码，用于去微信官方换取openid和session_key
		$rawData = Common_Util::getHttpReqQuery($this,'rawData','Str','n');//rawData
		$signature = Common_Util::getHttpReqQuery($this,'signature','Str','n','');//signature
		$encryptedData = Common_Util::getHttpReqQuery($this,'encryptedData','Str','n','');//encrytedData
		$iv = Common_Util::getHttpReqQuery($this,'iv','Str','n',''); //iv
		$referee_id = Common_Util::getHttpReqQuery($this,'referee_id','Int','y',0); //推荐人id
		$facility_system = Common_Util::getHttpReqQuery($this,'system','str','n',1);//设备系统
		if(strpos('c'.$facility_system,'iOS')!=false){
			$facility_system = 1;
		}elseif(strpos('c'.$facility_system,'Android')!=false){
			$facility_system = 2;	
		}else{
			$facility_system = 3;//未知系统
		}
		//根据code获取用户的openid，作为用户在系统里面的唯一标识
		$url = "https://api.weixin.qq.com/sns/jscode2session";
		$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		$post_data = [ 
				'grant_type' => 'authorization_code',
				'appid' => $config->partner->appid,
				'secret' => $config->partner->appsecret,
				'js_code' => $code
			];  
		$wxret = json_decode(Common_Util::RequestHttpArray('post',$url,$post_data),true);
		if (isset($wxret['openid']));; {//如果获取到openID则进行下一步处理，返回错误信息
			$result = $this->_model->getUserByOpenid($wxret['openid']);
			if (!empty($result)) {//用户表中有用户信息
				//print_r($result);exit;
				$rawdata = json_decode(html_entity_decode($rawData),true);
				//判断其头像和昵称有无修改有的话更新数据表,其余业务交给更新方法完成
				//用户如果更换过手机更新用户表facility_system字段
				if ($result['nickname'] != $rawdata['nickName'] || $result['avatarUrl'] != $rawdata['avatarUrl'] || $result['facility_system'] != $facility_system || $result['gender'] != $rawdata['gender']) {
					$this->updateUserInfo($wxret['openid'],$rawdata,$wxret,$facility_system);
					return false;
				}   
				unset($result['openid']);
				//登录成功判断用户学校填写
				if(!empty($result['school_id'])){
					$schoolname = $this->_model->getSchoolName($result['school_id']);
					$result['schoolname'] = $schoolname;
				}
				//登录成功之后写入session
				$result['sessionid'] = $this->writeSession($result['id'],$wxret);
				return Common_Util::returnJson('20001','登录成功',$result);
			}
			//用户表中没有用户信息则调用添加方法，剩下的业务逻辑交给添加去处理
			$this->addUser($wxret['openid'],$rawData,$wxret,$referee_id,$facility_system);
			return false;
		}
		return Common_Util::returnJson('20002','请重新授权',$wxret);

	}

	/**
	 * 如果用户昵称或者头像改变后，更新数据表中昵称或者头像的记录
	 */
	 private function updateUserInfo($openid,$rawdata,$wxret,$facility_system) {
		$data = [
				'nickname' => $rawdata['nickName'],
				'avatarUrl' => $rawdata['avatarUrl'],
				'facility_system'=>$facility_system,
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
	private function addUser($openid,$rawData,$wxret,$referee_id=0,$facility_system) {

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
			'referee_id' => $referee_id,
			'facility_system'=>$facility_system
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

 	/** 
     * 获取用户头像昵称
     */
	public function nickNameAction() {
		$sessionid = json_decode($this->_redis->get("aZt3YQsFpPsK6wp80cReJP3mTQ"),'array');	
		$userid    = $sessionid['user_id'];
		$nickname  = $this->_model->getNickname($userid);
		return Common_Util::returnJson('20001',$nickname);
		exit;	
	}
	
	/**
	 *获取用户个人资料(非微信)
	 */
	public function getUserinfoAction(){
		$sessionid = Common_Util::getHttpReqQuery($this,'sessionid','Str','n','');
		$sessionid = json_decode($this->_redis->get("$sessionid"),'array'); 
        $userid    = $sessionid['user_id'];
		$TrueUserinfo  = $this->_model->getTrueUserinfo($userid);	
	    return Common_Util::returnJson('20001',$TrueUserinfo);//不在判断数据是否为空

	}
	
	/** 
     * 个人资料信息收集
     */
	public function setUserinfoAction(){
		$data	   = [];
		$sessionid = Common_Util::getHttpReqQuery($this,'sessionid','Str','n','');
		$sessionid = json_decode($this->_redis->get("$sessionid"),'array'); 
        $userid    = $sessionid['user_id'];
		$data['realname'] = Common_Util::getHttpReqQuery($this,'realname','Str','n','');
		$schoolname	= Common_Util::getHttpReqQuery($this,'school','Str','n','');
		$data['enter_school_year'] = Common_Util::getHttpReqQuery($this,'enter_school_year','Str','n','');
		$data['mobilephone'] = Common_Util::getHttpReqQuery($this,'mobilephone','Str','n','');
		$data['update_userinfo_time'] = time();
		$Scode = Common_Util::getHttpReqQuery($this,'Scode','Str','n','');
		$Ycode = $this->_redis->get('Ycode'.$userid);
		$province = $this->_model->getStuProvince($schoolname);
		$schoolid = $this->_model->getSchoolId($schoolname);
		if($province && $schoolid){
			$data['province'] = $province['province'];
			$data['school_id'] = $schoolid;
		}else{
			return Common_Util::returnJson('10003','收集数据不完整');
			die;
		}
		if(!empty($Scode)){
				if(!empty($Ycode)){
						if($Ycode == $Scode){
								$status = $this->_model->UpUserinfo($data,$userid);
								if($status){
										return Common_Util::returnJson('20006','提交成功');
								}else{
										return Common_Util::returnJson('20003','提交失败');
								}
						}else{
								return Common_Util::returnJson('10003','验证码不正确');
						}	
				}else{
						return Common_Util::returnJson('10003','验证码已过期');
				}	
		}else{
			return Common_Util::returnJson('10003','验证码不能为空');
		}	
	}

	/**
	 *短信验证
	 */
	public function SMSVerificationAction(){
		$phone = Common_Util::getHttpReqQuery($this,'mobilephone','str','n','');
		$sessionid = Common_Util::getHttpReqQuery($this,'sessionid','Str','n','');
		$sessionid = json_decode($this->_redis->get("$sessionid"),'array'); 
        $userid    = $sessionid['user_id'];
		$subTime = $this->_redis->get('subTime'.$userid);
		$subphone = $this->_redis->get('subphone'.$userid);
		$isphone  = $this->_model->checkPhone($phone);
		if(!$isphone){
			return Common_Util::returnJson('10003','手机号已使用');	
		}
		if($phone=='undefined'){
			return Common_Util::returnJson('10003','手机号不能为空');
			die;
		}

		if(!preg_match("/^0?1[3|4|5|6|7|8][0-9]\d{8}$/", $phone)){
			return Common_Util::returnJson('10003','手机格式不正确');
		}

		if(!$subTime || $subphone != $phone){
				$arr = [0,1,2,3,4,5,6,7,8,9];
				$str = '';
				for ($i = 0; $i < 4; $i++){
						$str .= $arr[rand(0, 9)];
				}

				$this->_redis->setex('Ycode'.$userid,600,$str);
				$this->_redis->setex('subTime'.$userid,60,'A');
				$this->_redis->setex('subphone'.$userid,60,$phone);
				$Ycode = $this->_redis->get('Ycode'.$userid);
				$this->_model->smssend_chuanglan($phone,"您的验证码是$str");
				return Common_Util::returnJson('10002','发送短信成功');
		}

	}
	
	/**
	 *身份认证信息收集
	*/
	public function setIdentityAction(){
		$data = [];
		$sessionid = Common_Util::getHttpReqQuery($this,'sessionid','Str','n','');
		$sessionid = json_decode($this->_redis->get("$sessionid"),'array'); 
        $userid    = $sessionid['user_id'];
		$data['realname'] = Common_Util::getHttpReqQuery($this,'realname','Str','n','');
        $data['enter_school_year'] = Common_Util::getHttpReqQuery($this,'enter_school_year','Str','n','');
		$student_ID_url	= Common_Util::getHttpReqQuery($this,'student_ID_url','Str','n','');
		$data['student_ID_url'] = str_replace(',','|',$student_ID_url);
		$data['verifystatus'] = '1';
		$data['update_userinfo_time'] = time();
        $schoolname    = Common_Util::getHttpReqQuery($this,'school','Str','n','');
		$school_id = $this->_model->getSchoolId($schoolname);
		$data['school_id'] = $school_id;
		$status = $this->_model->UpUserinfo($data,$userid);
		if($status){
			return Common_Util::returnJson('20006','提交成功');
		}else{
			return Common_Util::returnJson('20003','提交失败');	
		}
	}
	
	/**
	 *身份认证上传图片
	 */
	public function imgUploadAction(){
		 //error_log(print_r($_FILES['file'],true));
		 if(isset($_FILES['file'])){
            $file = $_FILES['file'];
            /*  if(!empty($file)){
                    $size = $file['size'];
                    if(ceil($size/1024)>500){
                        echo json_encode(array('status'=>400,'msg'=>'图片过大,请上传小于500k图片'));
                        exit();
                    }
            }*/
            if(is_uploaded_file($file['tmp_name']))
            {
                $objmodel = new ObjectModel();
                $realname = $file['name'];
                $pos = strrpos($realname, ".");
                $postfix = substr($realname, $pos+1);
                $object_key = $objmodel->createPicObject($file['tmp_name'], $file['size'], $postfix);
                list($key, $bucket) = explode("|", $object_key);
                $prefix = 'http://img.stuhui.com';
                $imgsrc = "$prefix/$key";
				//error_log($imgsrc);
                echo  json_encode(array('status'=>200 , 'imgsrc'=>$imgsrc));
                exit();
            }else{
                echo json_encode(array('status'=>401,'msg'=>'参数错误'));
            }
        }else{
            echo "error";
        }
			
	}
	
	public function schoolNameAction(){
		$schoolname = $this->_model->getSchoolId();
		if($schoolname){	
			 return Common_Util::returnJson('20006','查询成功',$schoolname);
		}else{
			return Common_Util::returnJson('20006','查询失败',$schoolname);
		}
	}

	/**
	 * 账号状态信息
	 */	
	public function isforbiddenAction() {

		$user_id = $this->verifySessionid($this)['user_id'];
		$user_info = $this->_model->getUserInfo($user_id);
		if (empty($user_info) or $user_info['isforbidden'] === 2) {
			return Common_Util::returnJson('10008','账号异常');
		}
		return Common_Util::returnJson('20001','账号正常');
	}
	
	/**
	 * 添加用户推荐人
	 */
	public function addUserRefereeAction() {

		$user_id = $this->verifySessionid($this)['user_id'];
		$referee_id = Common_Util::getHttpReqQuery($this,'referee_id','Str','n','');
		$user_info = $this->_model->getUserInfo($user_id);
		if ($user_info['referee_id'] === 0  && $user_info['create_time'] + 15 > time() && $user_id != $referee_id) {
			$data = [
				'referee_id' => $referee_id	
				];
			$ret = $this->_model->UpUserinfo($data,$user_id);
			//var_dump($ret);
			if ($ret) {
				return Common_Util::returnJson('20006','添加推荐人成功');
			}
		}
		return Common_Util::returnJson('20007','添加推荐人失败');
	}

	/**
	 * 添加用户是否为首次登陆
	 */
	public function isFirstLoginingAction() {

		$user_id = $this->verifySessionid($this)['user_id'];
		$user_info = $this->_model->getUserInfo($user_id);
		switch($user_info['first_login']) {
			case 1:
				return Common_Util::returnJson('20002','首次登陆');
			case 2:
				return Common_Util::returnJson('10097','只访问过首页');
			case 4:
				return Common_Util::returnJson('10098','只访问过详情页');
			case 6:
				return Common_Util::returnJson('10099','新手步骤已经走完');
		}
		return Common_Util::returnJson('20001','非首次登陆');
		
	}

	/**
	 * 用户走完新手流程之后更新状态
	 */
	public function updateUserLoginStateAction() {

		$state = Common_Util::getHttpReqQuery($this,'state','Int','n');
		$user_id = $this->verifySessionid($this)['user_id'];
		$user_info = $this->_model->getUserInfo($user_id);
		$ret = false;
		switch ($user_info['first_login']) {
			case 1:
				$ret = $this->_model->UpUserinfo(['first_login' => $state],$user_id);
				break;
			case 2:
				if ($state == 4) {
					$ret = $this->_model->UpUserinfo(['first_login' => 6],$user_id);
				}
				break;
			case 4:
				if ($state == 2) { 
					$ret = $this->_model->UpUserinfo(['first_login' => 6],$user_id);
				}
				break;
		}
		//$ret = $this->_model->UpUserinfo(['first_login' => 2],$user_id);
		if ($ret) {
			return Common_Util::returnJson('20006','状态更新成功');
		}
		return Common_Util::returnJson('20007','状态更新失败');

	}
	

}
