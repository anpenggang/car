<?php 

/**
 *
 * @name UserModel
 * @desc 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version User.php v0.0 2017/9/30 新建
 */
class UserModel extends BaseModel {

	private $_table = 'barrage_users'; 

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
	public function getUserInfo($id) {
		
		//echo $openid;
		$sql = "SELECT * FROM {$this->_table} WHERE id='{$id}' LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		return $ret;	

	}
	
	/**
	 * 根据openID 获取用户信息
	 */
	public function getUserByOpenid($openid) {
	
		$sql = "SELECT * FROM {$this->_table} WHERE openid='{$openid}' LIMIT 1";
		$ret = $this->db->rawQuery($sql);
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
			$ret = $this->db->insert($this->_table,$data);
			return $ret;
		}

	}
	
	/**
	 * 更新用户头像或者昵称
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
	 * 在某次活动中禁用用户
	 */
	public function forbiddenActUser($user_id,$act_id) {

		$ret = $this->db->insert('barrage_act_userforbidden',['user_id' => $user_id,'act_id' => $act_id]);

		//屏蔽完用户将该用户之前发送关于该活动的弹幕状态全部设置为审核不通过
		if (is_numeric($ret) && (0 < $ret)) {
			$sql = "UPDATE barrage_content SET check_status = 2 WHERE act_id = '{$act_id}' AND user_id='{$user_id}'";
			$chang_check_status = $this->db->rawQuery($sql);
		}
		return $ret;

	}

	/**
	 * 判断某位用户在某次活动中是否被禁用
	 */
	public function isforbidden($user_id,$act_id) {

		$this->db->where('user_id',$user_id);
		$this->db->where('act_id',$act_id);
		$this->db->where('status',0);
		$ret = $this->db->getOne('barrage_act_userforbidden');
		return $ret;

	}

//class
}
