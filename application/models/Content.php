<?php

/**
 *
 * @name ContentModel
 * @desc 弹幕内容模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Content.php v0.0 2017/10/9 新建
 */
class ContentModel extends BaseModel {

	private $_table = 'barrage_content'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {
		
		//调用父类构造方法
		parent::__construct();		

	}

	/**
	 * 添加弹幕
	 *
	 * @param	Array	$data	弹幕内容
	 * @return  Mixed	$ret	如果成功返回新增弹幕ID;失败返回false
	 */
	public function addBarrageContent($data) {
		
		//特殊参数进行进一步过滤
		if (empty($data)) {
			return false;
		}
		$ret = $this->db->insert($this->_table,$data);
		return $ret;
			

	}

	/**
	 * 添加高级弹幕
	 *
	 * @param	Array	$data	弹幕内容
	 * @param	Array	$order	支付信息
	 * @return  Mixed	$ret	如果成功返回新增弹幕ID;失败返回false
	 */
	public function addSharpBarrageContent($data) {
		
		//特殊参数进行进一步过滤
		if (empty($data)) {
			return false;
		}
		$ret = $this->db->insert($this->_table,$data);
		return $ret;

	}

	/**
	 * 根据id获取单条弹幕的信息
	 *
	 * @param	Integer	$con_id 弹幕id
	 * @return	Array	$ret	单条弹幕的内容
	 */
	public function getBarrageContentById($con_id) {

		$sql = "SELECT c.*,a.roomnum,a.secret_key FROM {$this->_table} c";
		$sql .= " LEFT JOIN barrage_activity a ON c.act_id = a.id";
		$sql .= " WHERE c.id = '{$con_id}' LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		if (!empty($ret)) {
			return $ret[0];
		}
		return $ret;

	}

	/**
	 * 查询某个用户在某次活动中发送的弹幕
	 */
	public function showBarrageContentMySend($act_id,$user_id) {

		$this->db->where('act_id', $act_id);
		$this->db->where('user_id', $user_id);
		$this->db->orderBy('id', 'DESC');
		$ret = $this->db->get($this->_table,20);		
		return $ret;

	}

	/**
	 * 查询某个用户在某次活动中发送的弹幕，带分页
	 */
	public function showBarrageContentMySendWithPage($act_id,$user_id,$page,$size) {

		$limit_start = ($page-1)*$size;
		$sql = "SELECT * FROm {$this->_table} WHERE `act_id` = '{$act_id}' AND `user_id` = '{$user_id}' LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 返回用户发送的弹幕总数，用于分页显示数据量
	 */
	public function barrageContentMySendAll_num($act_id,$user_id ) {

		$this->db->where('act_id', $act_id);
		$this->db->where('user_id',$user_id);
		$result = $this->db->get($this->_table,null,'id');
		return count($result);

	}

	/**
	 * 显示弹幕
	 * 对于被屏蔽的用户已发送弹幕的处理 
	 * 1 可以在查询结果集中过滤 (数据库请求压力大，要对每条数据去数据库匹配)
	 * 2.直接在屏蔽该用户时将其所有关于本活动的弹幕状态改为审核不通过,
	 *   且其以后所有发送的信息状态均为为通过（目前系统采用此方案）
	 * 3.在查询条件中进行排除
	 */
	public function showBarrageContent($data) {
		
		//显示弹幕
		$sql = "SELECT c.*,u.nickname FROM {$this->_table} c LEFT JOIN barrage_users u ON c.user_id = u.id"; 
		$sql .= " WHERE c.act_id = {$data['act_id']} and c.check_status = {$data['check_status']} ORDER BY c.id ASC LIMIT 10";
		$bar_contents = $this->db->rawQuery($sql);
		
		//对查询出的结果进行过滤，如果该用户在活动的屏蔽列表，就unset对应的信息
		//$user_model = new UserModel();
		//foreach($bar_contents as $key => $bar_content) {
		//	$isforbidden = $user_model->isforbidden($bar_content['user_id'],$bar_content['act_id']);
		//	if (!empty($isforbidden)) {
		//		unset($bar_contents[$key]);
		//	}
		//}
		return $bar_contents;

	}
	
	/**
	 * 获取用户参与的弹幕信息
	 *
	 * @param	Integer	$user_id	用户ID
	 * @return	Mixed	$ret		成功返回用户参与的弹幕;失败时返回false
	 */	
	public function getMyParticipate($user_id) {
		
		//从弹幕信息表里面获取该用户的弹幕对应的活动ID
		$sql = "SELECT DISTINCT act_id FROM {$this->_table} WHERE user_id = '{$user_id}'";
		$act_ids = $this->db->rawQuery($sql);
		if(count($act_ids) == 0) {
			Common_Util::returnJson('20002','该用户没用参加活动');
			exit();

		}
		// 将获取出来的活动ID拼接成字符串查表，避免循环中查表
		$ids = '';
		foreach ($act_ids as $act_id) {
			$ids .= $act_id['act_id'].',';
		}	
		$ids = rtrim($ids,',');
		//根据获取到的活动ID，查询对应的用户参与的活动和该用户创建的活动
		$sql = "SELECT id,name,status,create_time FROM barrage_activity WHERE id IN ({$ids}) OR user_id = '{$user_id}' ORDER BY id DESC";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 审核弹幕
	 *
	 * @param	Array	$data	弹幕审核内容
	 * @return	Boolean	$ret	成功返回true，否则返回false
	 */
	public function verifyBarrageContent($data) {

		$this->db->where('id',$data['con_id']);
		$ret = $this->db->update($this->_table,['check_status'=>$data['check_status']]);
		return $ret;
	}
	
	/**
	 * 获取活动是否开启审核
	 *
	 * @param	Integer	$act_id	活动ID
	 * @return	Array	$ret	活动审核状态
	 */
	public function isCheckable($act_id) {

		$sql = "SELECT bar_check FROM barrage_activity where id = '{$act_id}'";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	public function testapg() {
		$this->db->where('id',153);
		$ret = $this->db->getOne($this->_table);
		return $ret;
	
	}

}

