<?php

/**
 *
 * @name PtnTaskModel
 * @desc partner_task_model 任务模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Activity.php v0.0 2018/3/5 新建
									   添加 getTaskNames 方法
 */
class PtnTaskModel extends BaseModel {

	private $_table = 'partner_minipro_task';

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 获取任务列表（带分页）
	 *
	 * @param	Integer		$user_id 			用户id
	 * @param	String		$accepted_task_ids	用户已经接受任务id
	 * @param	Integer		$page				第几页
	 * @param	Integer		$$size				每页显示数量
	 * @return	Array		$ret				任务项数组
	 */
	public function getAcceptableTaskList($user_id,$accepted_task_ids,$page,$size) {

		$limit_start = ($page-1)*$size;
		$now_time = time();
		//如果用户有已经接受的任务，则不在本次查询
		$where = !empty($accepted_task_ids) ? " AND id NOT IN ($accepted_task_ids)" : "";
		$sql = "SELECT t.id,t.name,t.end_time,t.pay_method,t.reward FROM {$this->_table} t";
		$sql .= " WHERE t.status = 1 AND t.end_time > {$now_time} AND t.start_time < {$now_time} AND t.executers >= (SELECT COUNT(1) FROM partner_minipro_user_task ut WHERE ut.task_id=t.id)".$where;
		$sql .= " ORDER BY end_time DESC";
		$sql .= " LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取可接受任务总数
	 *
	 * @param	Integer 	$user_id					用户id
	 * @param	String		$accepted_task_ids			用户已经接受任务id
	 * @return	Array									任务项数组
	 */
	public function getAcceptableTaskCount($user_id,$accepted_task_ids) {

		$now_time = time();
		//如果用户有已经接受的任务，则不在本次查询
		$where = !empty($accepted_task_ids) ? " AND id NOT IN ($accepted_task_ids)" : "";
		$sql = "SELECT COUNT(1) AS count  FROM {$this->_table}";
		$sql .= " WHERE status = 1 AND end_time > {$now_time}".$where;
		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取任务详情
	 *
	 * @param	Integer		$task_id		任务id
	 * @return	Array		$ret			返回任务详情
	 */
	public function getTaskDetail($task_id,$user_id) {

		$sql = "SELECT t.*,IFNULL(rt.status,9) AS review_status,IFNULL(ut.status,9) AS task_status FROM {$this->_table} t";
		$sql .= " LEFT JOIN partner_minipro_review_task rt ON rt.task_id={$task_id} AND rt.user_id={$user_id}";
		$sql .= " LEFT JOIN partner_minipro_user_task ut ON ut.task_id={$task_id} AND ut.user_id={$user_id}";
		$sql .= " WHERE t.id={$task_id} LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		return !empty($ret)?$ret[0] : false;

	}
	
	/**
	 * 获取单个独立任务详情
	 *
	 * @param	Integer		$task_id	任务id
	 * @return	Array		$ret		任务详情
	 */
	public function getTaskInfo($task_id) {

		$this->db->where('id',$task_id);
		return $this->db->getOne($this->_table);

	}
	
	/**
	 * 获取任务列表（带分页）
	 *
	 * @param	Integer		$user_id 			用户id
	 * @param	String		$accepted_task_ids	用户已经接受任务id
	 * @param	Integer		$page				第几页
	 * @param	Integer		$$size				每页显示数量
	 * @return	Array		$ret				任务项数组
	 */
	public function getMyAcceptableTaskList($user_id,$accepted_task_ids,$page,$size) {

		//用户资料完善后才能接单
		$user_model = new PtnUserModel();
		$user_info = $user_model->getUserInfo($user_id);

		if ($user_info['school_id'] == 0) {
			if ($page >= 2) return ;
			$limit_start = ($page-1)*$size;
			$now_time = time();

			$sql = "SELECT t.id,t.name,t.end_time,t.pay_method,t.reward FROM {$this->_table} t";
			$sql .= " WHERE t.status = 1 AND t.end_time > {$now_time} AND t.start_time < {$now_time} AND t.executers > (SELECT COUNT(1) FROM partner_minipro_user_task ut WHERE ut.task_id=t.id) AND t.req_region=0 AND t.req_sex=0 AND t.req_device=0";
			$sql .= " ORDER BY t.end_time ASC,t.id DESC LIMIT 20";
			$ret = $this->db->rawQuery($sql);
			$ret_count = 20 - count($ret);

			$task_ids = implode(',',array_column($ret,'id'));
			if ($ret_count < 20) {
				$sql2 = "SELECT t.id,t.name,t.end_time,t.pay_method,t.reward FROM {$this->_table} t";
				$sql2 .= " WHERE t.status = 1 AND t.end_time > {$now_time} AND t.start_time < {$now_time} AND t.executers > (SELECT COUNT(1) FROM partner_minipro_user_task ut WHERE ut.task_id=t.id) AND (t.req_region != 0 OR t.req_sex != 0 OR t.req_device != 0) AND t.id NOT IN ({$task_ids})";
				$sql2 .= " ORDER BY t.reward ASC,t.id DESC LIMIT 0,{$ret_count}";
				$ret2 = $this->db->rawQuery($sql2);

				$ret = array_merge($ret,$ret2);
			}
			return $ret;			

		}

		//查询用户所对应的
		$school_model = new PtnSchoolModel();
		$school_info = $school_model->getLocation($user_info['school_id']);

		$limit_start = ($page-1)*$size;
		$now_time = time();
		//如果用户有已经接受的任务，则不在本次查询
		$where = !empty($accepted_task_ids) ? " AND id NOT IN ($accepted_task_ids)" : "";
		$sql = "SELECT t.id,t.name,t.end_time,t.pay_method,t.reward FROM {$this->_table} t";
		$sql .= " WHERE t.status = 1 AND t.end_time > {$now_time} AND t.start_time < {$now_time} AND t.executers > (SELECT COUNT(1) FROM partner_minipro_user_task ut WHERE ut.task_id=t.id)".$where;
		$sql .= " AND (CASE WHEN t.req_region = 0 THEN 1=1
					WHEN t.req_region = 1 AND t.req_province = '{$school_info['province']}' THEN 1=1
					WHEN t.req_region = 2 AND t.req_city = '{$school_info['city_id']}' THEN 1=1
					WHEN t.req_region = 3 AND t.req_school = '{$school_info['id']}' THEN 1=1 
					ELSE 0
					END) ";//地区
		$sql .= " AND (CASE WHEN t.req_sex = 0 THEN 1=1
					WHEN t.req_sex = 1 AND {$user_info['gender']} = 1 THEN 1=1
					WHEN t.req_sex = 2 AND {$user_info['gender']} = 2 THEN 1=1
					ELSE 0
					END)";//性别
		$sql .= " AND (CASE WHEN t.req_device = 0 THEN 1=1
					WHEN t.req_device = 1 AND {$user_info['facility_system']} = 1 THEN 1=1
					WHEN t.req_device = 2 AND {$user_info['facility_system']} = 2 THEN 1=1 
					END)";//设备
		$sql .= " ORDER BY end_time DESC";
		$sql .= " LIMIT {$limit_start},{$size}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取某个任务的接单人数
	 */
	public function getAcceptersNum($task_id) {

		$sql = "SELECT COUNT(1) as count FROM partner_minipro_user_task WHERE task_id={$task_id}";
		$ret = $this->db->rawQuery($sql);
		return $ret[0];

	} 

	/**
	 * 获取任务名称
	 *
	 * @param	Array 	$task_ids	任务id数组
	 * @return	Array	$ret		任务名称数组
	 */
	public function getTaskNames($task_ids) {

		$task_ids_str = implode(',', $task_ids);
		$sql = "SELECT name FROM {$this->_table} WHERE id in ({$task_ids_str})";
		$ret = $this->db->rawQuery($sql);
		return $ret;	

	}

}//class

