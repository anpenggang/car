<?php 

/**
 *
 * @name CdpOAUserModel
 * @desc CountdownParty_user 公众号用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CdpOAUser.php v0.0 2017/12/19 新建
 */
class CdpOAUserModel extends BaseModel {

	private $_table = 'countdownparty_oa_user'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() { 
		
		//调用父类构造方法
		parent::__construct();

	}

	/**
 	 * 获取用户信息 
	 *
	 * @param 	String	$id 	用户ID
	 * @return 	Array 	$ret	用户信息
	 */
	public function getUserInfoById($id) {
		
		//echo $openid;
		$this->db->where('id',$id);
		$ret = $this->db->getOne($this->_table);
		return $ret;	

	}
	
	/**
	 * 根据openID 获取用户信息
	 *
	 * @param String $openid openid.
	 * @return Mixed  $ret 成功时返回用户信息数组，失败时返回false
	 */
	public function getUserInfoByOpenid($openid) {
	
		$this->db->where('openid',$openid);
		$ret = $this->db->getOne($this->_table);
		return $ret;

	}

	/** 
	 * 根据unionid获取用户信息
	 */
	public function getUserInfoByUnionid($unionid) {
	
		$this->db->where('unionid',$unionid);
		$ret = $this->db->getOne($this->_table);
		return $ret;

	}
	
	/**
	 * 添加用户
	 *
	 * @param	Array	$data	新建用户信息
	 * @return	Mixed	$ret	如果成功返回新增用户ID;失败返回false
	 */	
	public function addUser($data) {

		//参数判断
		if (!empty($data)) {
			$user_info = [
				'nickname' => $data['nickname'],
				'sex' => $data['sex'],
				'city' => $data['city'],
				'headimgurl' => $data ['headimgurl'],
				'unionid' => $data['unionid'],
				'openid' => $data['openid'],
				'subscribe' => $data['subscribe'],
				'create_time' => time(),
				'subscribe_time' => $data['subscribe_time'],
				'groupid' => $data['groupid']
			];
			$ret = $this->db->insert($this->_table,$user_info);
			return $ret;
		}

	}
	
	/**
	 * 更新用户头像或者昵称
	 *
	 * @param	string	$openid	openid.
	 * @param	array	$data	用户需要更新的数据数组
	 * @return	mixed	$ret/$result	成功时返回用户信息，失败时返回false
	 */
	public function updateUserInfo($openid,$data) {

		$this->db->where('openid',$openid);
		$ret = $this->db->update($this->_table,$data);
		if($ret) {
			$result = $this->getUserByOpenid($openid);
			return $result;
		}
		return $ret;
	}

	/**
	 * 在活动中禁用用户
	 *
	 * @param	integer	$user_id 用户id
	 * @return	boolean	$ret	成功时返回true，失败返回false
	 */
	public function forbiddenUser($user_id) {

		$this->db->where('id',$user_id);
		$user_ret = $this->db->update($this->_table,['isforbidden' => 1]);
		if ($user_ret) {
			//禁用该用户成功之后，将该用户已发所有未审核弹幕的状态改为审核未通过
			$this->db->where('user_id',$user_id);
			$this->db->where('status',0);
			$danmu_ret = $this->db->update('countdownparty_danmaku',['status'=>9]);
			var_dump($danmu_ret);
		}

		if($user_ret) {
			return true;
		}
		return false;

	}

	/**
	 * 判断某位用户在某次活动中是否被禁用
	 * 
	 * @param 	integer	$user_id	用户id
	 * @return	array	$ret		返回用户信息数组
	 */
	public function isforbidden($user_id) {

		$this->db->where('id',$user_id);
		$this->db->where('isforbidden',1);
		$ret = $this->db->getOne($this->_table,'id');
		return $ret;

	}

	/**
	 * 判断某位用户是不是超级用户
	 *
	 * @param	Integer	$user_id	用户id
	 * @return	Array	$ret		返回用户信息数组
	 */
	public function issupperUser($user_id) {
	
		$this->db->where('id',$user_id);
		$this->db->where('issupper',6);
		$ret = $this->db->getOne($this->_table,'id');
		return $ret;
	
	}

	/**
	 * 获取管理员列表
	 *
	 * @return Array $ret 管理员列表
	 */
	public function listManager() {

		$this->db->where('issupper',6);
		$ret = $this->db->get($this->_table);
		return $ret;

	}
	
	/**
	 * 更新公众号用户订阅状态
	 */
	public function updateSubscribe($openid,$status) {
	
		$this->db->where('openid',$openid);
		$ret = $this->db->update($this->_table,['subscribe' => $status]);//0表示取关，1 表示关注
		return $ret;			

	}

	/**
	 * 
	 */
	public function insertNickname() {

		$sql1 = "select * from countdownparty_nickname_user";
		$ret = $this->db->rawQuery($sql1);
		$nicknames = implode(',',array_column($ret,'nickname'));
		$sql = "select * from countdownparty_oa_user where nickname in ({$nicknames})";
		$res = $this->db->rawQuery($sql);
		return $res;
		
	}

//class
}
