<?php 

/**
 *
 * @name CdpSubscribe
 * @desc CountdownParty_Subscribe用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CdpSubscribe.php v0.0 2017/12/19 新建
 */
class CdpSubscribeModel extends BaseModel {

	private $_table = 'countdownparty_subscribe'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() { 
		
		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 新增订阅方法
	 *
	 * @param 	Array 	$param	新增订阅数据
	 * @return 	Mixed 	$res 	成功返回新增id，失败返回false
	 */	
	public function addSubscribe($param) {
		
		$ret = $this->db->insert($this->_table,$param);		
		return $ret;

	}

	/**
	 * 查询用于订阅信息
	 *
	 * @param 	Integer		$user_id	用户id
	 * @return	Array		$ret		用户订阅信息数组，用户没有订阅时显示为空数组
	 */
	public function listSubscribe($user_id) {
	
		$this->db->where('user_id',$user_id);
		$ret = $this->db->getOne($this->_table);
		return $ret;		

	}

	/**
	 * 显示院校对应的预约人数
	 */
	public function collegesSubscribes($page,$size) {

		$page = intval($page);
		$size = intval($size);
		$limit_start = ($page-1)*$size;
		// LIMIT $limit_start $size

		$sql = "SELECT COUNT(*) AS count,school FROM $this->_table GROUP BY (school) LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	/**
	 * 显示不同院校信息（有用户订阅）
	 */
	public function selectAllNum() {

		$sql = "SELECT DISTINCT school FROM $this->_table";
		$ret = $this->db->rawQuery($sql);
		return count($ret);

	}

	/**
	 * 预约用户详细信息
	 */
	public function subscribeUserInfo($page,$size){

		$page = intval($page);
		$size = intval($size);
		$limit_start = ($page-1)*$size;
		// LIMIT $limit_start $size

		$sql = "SELECT s.id,s.school,s.phone,s.create_time,u.nickname,u.avatarurl FROM $this->_table s";
		$sql .= " LEFT JOIN countdownparty_minipro_user u ON s.user_id = u.id LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 预约用户总数
	 */
	public function subscribeUserInfoCount() {

		$sql = "select count(*) as count from {$this->_table}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
//class
}
