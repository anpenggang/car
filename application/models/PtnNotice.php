<?php

/**
 *
 * @name PtnNotice.php
 * @desc partner_notice 通知模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnNotice.php v0.0 2018/3/7 新建
							   2018/3/29 添加readedAllNotice方法，标记所有消息为已读
 */
class PtnNoticeModel extends BaseModel {

	private $_table = 'partner_minipro_notice';
	private $_relation_table = 'partner_minipro_user_notice';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 获取用户通知
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$page		页码
	 * @param	Integer		$size		每页显示数量
	 * @return	Array		$ret		通知消息
	 */
	public function getNotices($user_id,$page,$size) {

		//用户资料完善后才能接单
        $user_model = new PtnUserModel();
        $user_info = $user_model->getUserInfo($user_id);

		//如果用户信息表中没有schoolid 将学些信息全部置为0
		if (empty($user_info['school_id'])) {
			$school_info = [
				'province' => 0,
				'city_id' => 0,
				'id' => 0
			];
		} else {
        	//查询用户所对应的学校信息
        	$school_model = new PtnSchoolModel();
        	$school_info = $school_model->getLocation($user_info['school_id']);
		}
		$limit_start = ($page-1)*$size;
		$now_time = time();
		$sql = "SELECT n.id,n.type,n.content,n.create_time,n.publish_time,IFNULL(un.isread,0) as isread,IFNULL(un.isdel,0) as isdel FROM {$this->_table} n";
		$sql .= " LEFT JOIN {$this->_relation_table} un ON n.id=un.notice_id AND un.user_id={$user_id}";
		$sql .= " WHERE (type=0 OR n.user_id={$user_id}) AND n.isdel=0 AND publish_time < {$now_time} AND n.id NOT IN (SELECT notice_id FROM {$this->_relation_table} WHERE isdel=1 or isread=1)";	
		$sql .= " AND (CASE WHEN n.req_region = 0 THEN 1=1
					WHEN n.req_region = 1 AND n.req_province = '{$school_info['province']}' THEN 1=1
					WHEN n.req_region = 2 AND n.req_city = '{$school_info['city_id']}' THEN 1=1
					WHEN n.req_region = 3 AND n.req_school = '{$school_info['id']}' THEN 1=1 
					ELSE 0
					END) ";//地区
		$sql .= " AND (CASE WHEN n.req_sex = 0 THEN 1=1
					WHEN n.req_sex = 1 AND {$user_info['gender']} = 1 THEN 1=1
					WHEN n.req_sex = 2 AND {$user_info['gender']} = 2 THEN 1=1
					ELSE 0
					END)";//性别
		$sql .= " AND (CASE WHEN n.req_device = 0 THEN 1=1
					WHEN n.req_device = 1 AND {$user_info['facility_system']} = 1 THEN 1=1
					WHEN n.req_device = 2 AND {$user_info['facility_system']} = 2 THEN 1=1 
					END)";//设备
		$sql .= " ORDER BY n.publish_time DESC";
		$sql .= " LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取用户消息 不分已读未读
	 */
	public function getNotice($user_id,$page,$size) {

		//用户资料完善后才能接单
        $user_model = new PtnUserModel();
        $user_info = $user_model->getUserInfo($user_id);

		if (empty($user_info)) {
			return [];
		}
		//如果用户信息表中没有schoolid 将学些信息全部置为0
		if (empty($user_info['school_id'])) {
			$school_info = [
				'province' => 0,
				'city_id' => 0,
				'id' => 0
			];
		} else {
        	//查询用户所对应的学校信息
        	$school_model = new PtnSchoolModel();
        	$school_info = $school_model->getLocation($user_info['school_id']);
		}
		$limit_start = ($page-1)*$size;
		$now_time = time();
		$sql = "SELECT n.id,n.type,n.content,n.create_time,n.publish_time FROM {$this->_table} n";
		$sql .= " WHERE (type=0 OR n.user_id={$user_id}) AND n.isdel=0 AND publish_time < {$now_time}";	
		$sql .= " AND (CASE WHEN n.req_region = 0 THEN 1=1
					WHEN n.req_region = 1 AND n.req_province = '{$school_info['province']}' THEN 1=1
					WHEN n.req_region = 2 AND n.req_city = '{$school_info['city_id']}' THEN 1=1
					WHEN n.req_region = 3 AND n.req_school = '{$school_info['id']}' THEN 1=1 
					ELSE 0
					END) ";//地区
		$sql .= " AND (CASE WHEN n.req_sex = 0 THEN 1=1
					WHEN n.req_sex = 1 AND {$user_info['gender']} = 1 THEN 1=1
					WHEN n.req_sex = 2 AND {$user_info['gender']} = 2 THEN 1=1
					ELSE 0
					END)";//性别
		$sql .= " AND (CASE WHEN n.req_device = 0 THEN 1=1
					WHEN n.req_device = 1 AND {$user_info['facility_system']} = 1 THEN 1=1
					WHEN n.req_device = 2 AND {$user_info['facility_system']} = 2 THEN 1=1 
					END)";//设备
		$sql .= " ORDER BY n.publish_time DESC";
		$sql .= " LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取用户通知数 (用于分页的总数)
	 *
	 * @param	Integer		$user_id	用户id
	 * @return	Array		$ret		包含分页总数的数组
	 */
	public function getNoticeCount($user_id) {

		//用户资料完善后才能接单
        $user_model = new PtnUserModel();
        $user_info = $user_model->getUserInfo($user_id);

		if (empty($user_info)) {
			return [0=>['count' => 0]];
		}
		//如果用户信息表中没有schoolid 将学些信息全部置为0
		if (empty($user_info['school_id'])) {
			$school_info = [
				'province' => 0,
				'city_id' => 0,
				'id' => 0
			];
		} else {
        	//查询用户所对应的学校信息
        	$school_model = new PtnSchoolModel();
        	$school_info = $school_model->getLocation($user_info['school_id']);
		}
		$now_time = time();
		$sql = "SELECT COUNT(1) as count FROM {$this->_table} n";
		$sql .= " WHERE (type=0 OR n.user_id={$user_id}) AND n.isdel=0 AND publish_time < {$now_time} AND n.id NOT IN (SELECT notice_id FROM {$this->_relation_table} WHERE isdel=1 OR isread=1)";
		$sql .= " AND (CASE WHEN n.req_region = 0 THEN 1=1
					WHEN n.req_region = 1 AND n.req_province = '{$school_info['province']}' THEN 1=1
					WHEN n.req_region = 2 AND n.req_city = '{$school_info['city_id']}' THEN 1=1
					WHEN n.req_region = 3 AND n.req_school = '{$school_info['id']}' THEN 1=1 
					ELSE 0
					END) ";//地区
		$sql .= " AND (CASE WHEN n.req_sex = 0 THEN 1=1
					WHEN n.req_sex = 1 AND {$user_info['gender']} = 1 THEN 1=1
					WHEN n.req_sex = 2 AND {$user_info['gender']} = 2 THEN 1=1
					ELSE 0
					END)";//性别
		$sql .= " AND (CASE WHEN n.req_device = 0 THEN 1=1
					WHEN n.req_device = 1 AND {$user_info['facility_system']} = 1 THEN 1=1
					WHEN n.req_device = 2 AND {$user_info['facility_system']} = 2 THEN 1=1 
					END)";//设备
		//$sql .= " ORDER BY n.create_time DESC";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 用户点击阅读通知，向阅读表中新增一条已阅读记录
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$notice_id	通知id
	 * @return	Mixed		$ret		成功返回新增id,失败返回false
	 */
	public function readedNotice($user_id,$notice_id) {

		$data = [
			'user_id' => $user_id,
			'notice_id' => $notice_id,
			'isread' => 1,
			'create_time' => time()
		];

		$ret = $this->db->insert($this->_relation_table,$data);
		return $ret;

	}

	/**
	 * 判断某条消息的状态是否为未读
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$notice_id	通知id
	 * @return	Array		$ret		未读返回空数组，已读返回数据详情
	 */
	public function isReadedNotice($user_id,$notice_id) {

		$ret = $this->db->getOne($this->_relation_table,['id','notice_id','isread']);
		return $ret;

	}

	/**
	 * 设置某条消息状态为已读
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$notice_id	通知id
	 * @return	Boolean		$ret		成功返回true，失败返回false
	 */
	public function setToReaded($user_id,$notice_id) {

		$this->db->where('user_id',$user_id)->where('notice_id',$notice_id);
		$ret = $this->db->update($this->_relation_table,['isread' => 1]);
		return $ret;
	}

	/**
	 * 设置某条消息状态为已删除
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$notice_id	通知id
	 * @return	Boolean		$ret		成功返回true，失败返回false
	 */
	public function delNotice($user_id,$notice_id) {
	
		$this->db->where('user_id',$user_id)->where('notice_id',$notice_id);
		//如果关联表中没有记录的话，则直接插入
		if (empty($this->db->getOne($this->_relation_table))) {
			return $this->db->insert($this->_relation_table,['user_id'=>$user_id,'notice_id'=>$notice_id,'isdel'=>1,'create_time'=>time()]);
		}
		$this->db->where('user_id',$user_id)->where('notice_id',$notice_id);
		$ret = $this->db->update($this->_relation_table,['isdel' => 1]);
		return $ret;
	
	}

	/**
	 * 将用户消息全部标记为未读
	 */
	public function readedAllNotice($user_id) {

		$user_notices = $this->getNotices($user_id, 1, 100000);
		if (empty($user_notices)) return;	
		foreach ($user_notices as $user_notice) {
			$insert_data = [
				'user_id' => $user_id,
				'notice_id' => $user_notice['id'],
				'isread' => 1,
				'create_time' => time()
				];
			$this->db->insert('partner_minipro_user_notice', $insert_data);	
		}

	}

}//class
