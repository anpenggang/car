<?php

/**
 * 
 * @name PtnUserTask.php
 * @desc Partner_user_task 用户任务关联模型(已接任务模型)
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnUserTask.php v0.0 2018/3/6 新建
 * @version PtnUserTask.php v0.1 2018/2/26 添加惩罚用户方法
 */
class PtnUserTaskModel extends BaseModel {

	private $_table = 'partner_minipro_user_task';
	
	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 获取用户已经接单的任务id
	 *
	 * @param	Integer		$userid		用户id
	 * @return	Array		$task_ids	任务id数组
	 */
	public function getUserAcceptedTaskIds($user_id) {

		$this->db->where('user_id',$user_id);
		$task_ids = $this->db->get($this->_table,null,'task_id');
		return $task_ids;

	}

	/**
	 * 获取用户已经接单的任务列表
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$page		第几页
	 * @param	Integer		$size		每页显示数量
	 * @return	Array		$ret		用户已接单任务列表
	 */
	public function getAcceptedTaskList($user_id,$page,$size) {

		$limit_start = ($page-1)*$size;
		$sql = "SELECT t.name,t.end_time,ut.task_id,t.reward,t.pay_method,IFNULL(rt.status,9) AS review_status FROM {$this->_table} ut";
		$sql .= " LEFT JOIN partner_minipro_task t ON ut.task_id = t.id";
		$sql .= " LEFT JOIN partner_minipro_review_task rt ON ut.task_id = rt.task_id AND rt.user_id={$user_id}";
		$sql .= " WHERE ut.user_id = {$user_id}";
		//$sql .= " ORDER BY t.end_time ASC,t.id DESC";
		$sql .= " ORDER BY (CASE 
					WHEN ut.status = 2 THEN 0 ELSE 1 END),t.end_time DESC,t.id DESC";
		/*
		order by (
    		case
     			when id=263 then 1 ELSE 4 END),category_id desc;
		*/
		$sql .= " LIMIT {$limit_start},$size";

		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取用户已经接单的任务总数
	 *
	 * @param	Integer		$user_id	用户id
	 * @return	Array		$ret		用户已接单任务总数
	 */
	public function getAcceptedTaskListCount($user_id) {

		$sql = "SELECT COUNT(1) AS count FROM {$this->_table} WHERE user_id={$user_id}";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/** 
     * 接单
     *
     * @param   Integer     $data   需要添加的数据
     * @return  Mixed       $ret    成功返回新增id失败范湖false
     */
    public function addUserTask($data) {
    
        $ret = $this->db->insert($this->_table,$data);
		return $ret;

    }

	/**
	 * 查询用户是否已经接单某个任务
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$task_id	任务id
	 * @return	Array		$ret		已接单任务详情
	 */
	public function checkAccepted($user_id,$task_id) {

		$this->db->where('user_id',$user_id)->where('task_id',$task_id);
		$ret = $this->db->getOne($this->_table,['id','user_id','task_id','status','create_time']);
		return $ret;

	}

	/**
	 * 获取用户未完成任务，且未接受惩罚的任务
	 *
	 * @param		Integer		$user_id		用户id
	 * @return		Array		$ret			用户未完成任务列表
	 */
	public function getUnfinishedTasks($user_id) {
	
		$now = time();
		//从任务列表中获取用户未完成任务列表
		$sql = "SELECT ut.*,t.end_time FROM {$this->_table} ut";
		$sql .= " INNER JOIN partner_minipro_task t ON t.id = ut.task_id AND t.end_time < {$now}";
		$sql .= " WHERE ut.ispunished = 1 AND ut.user_id = {$user_id} AND ut.task_id NOT IN (SELECT task_id from partner_minipro_review_task where user_id={$user_id})";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 对用户三次未完成任务进行封禁一周的惩罚
	 *
	 * @param		Integer		$user_id	用户id
	 * @param		Array		$task_ids	未完成任务id数组
	 * @return		Boolean		$ret		封禁成功返回true,其他情况返回false
	 */
	public function publishingUser($user_id, $task_ids,$forbidden_endtime,$task_names_str) {

		//开启事务
		$this->db->autocommit(false);
		$task_ids_str = implode(',', $task_ids);

		//去任务表中更新任务惩罚状态为已经惩罚
		$sql = "UPDATE {$this->_table} set ispunished = 2 WHERE user_id = {$user_id} AND task_id IN ($task_ids_str)";
		$ut_ret = $this->db->rawUpdate($sql);

		//去用户表中修改认证状态为认证失败
		$this->db->where('id',$user_id);
		$u_ret = $this->db->update('partner_minipro_user',['verifystatus' => 3, 'isforbidden' => 1, 'forbidden_endtime' => $forbidden_endtime, 'forbidden_reason' => "超过三次未完成任务，临时封禁一周"]);

		//去通知表中添加一条新纪录
		$notice_data = [
			'type' => 1,
			'user_id' => $user_id,
			'content' => '您在任务'.$task_names_str.'中,接单后未完成任务，现将您账户封禁7天，7天后自动解封',//此处需要列出未完成任务的三次任务名称
			'create_time' => time(),
			'publish_time' => time()
			];
		$n_ret = $this->db->insert('partner_minipro_notice',$notice_data);
		if ($ut_ret && $u_ret && $n_ret) {
			$this->db->commit();
            return true;
		}
		//回滚
		$this->db->rollback();
		return false;

	}

}
