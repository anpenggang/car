<?php

/**
 *
 * @name PtnReviewContent.php
 * @desc partner_review_content 审核内容模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnReviewContent.php v0.0 2018/3/8 新建
 */
class PtnReviewContentModel extends BaseModel {

	private $_table = 'partner_minipro_review_content';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 添加审核项
	 *
	 * @param	Array	$data	需要添加的项
	 * @return	Mixed	$ret	成功返回新增项的id，失败返回false
	 */
	public function addReviewContent($data) {

		//开启事务
		$this->db->autocommit(false);

		if (!empty($data['menu_item'])) {
			$ret = 0;
			//error_log(print_r($data['menu_item'],true));
			foreach ($data['menu_item'] as $key => $value) {
				//echo $value['menu_value'];exit;
				$insert_data = [
					'user_id' => $data['user_id'],
					'task_id' => $data['task_id'],
					'menu_id' => $value['menu_id'],
					'menu_value' => $value['menu_value'],
					'create_time' => time()
				];
				$ret += $this->db->insert($this->_table,$insert_data);
			} 
		}

		$rt_res = $this->db->insert('partner_minipro_review_task',['user_id' => $data['user_id'],'task_id' => $data['task_id'],'create_time'=>time()]);

		if ($ret > 0 && $rt_res > 0) {
			$this->db->commit();
			return true;
		}
		//回滚
		$this->db->rollback();
		return false;

	}

	/**
	 * 获取已经提交的审核项，用户回填审核页面
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$task_id	任务id
	 * @return	Array		$ret		审核项内容
	 */
	public function getReviewedContent($user_id,$task_id) {

		$sql = "SELECT rm.id AS menu_id,rm.name,rm.type,rm.intro,rm.example_pic,IFNULL(rc.menu_value,'') AS menu_value FROM partner_minipro_review_menu rm";
		$sql .= " LEFT JOIN {$this->_table} rc ON rm.id=rc.menu_id AND rc.task_id={$task_id} AND rc.user_id={$user_id}";
		$sql .= " WHERE rm.task_id={$task_id}";
		$sql .= " ORDER BY type asc";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 更新审核项内容
	 *
	 * @param	Array		$data		需要审核的项
	 * @return	Boolean		$ret		成功返回true，失败返回false
	 */
	public function updateReviewContent($data) {

		//开启事务
		$this->db->autocommit(false);
		$i = 0;
		if (!empty($data['menu_item'])) {
			$ret = 0;
			foreach ($data['menu_item'] as $key => $value) {
				$this->db->where('user_id',$data['user_id'])->where('task_id',$data['task_id'])->where('menu_id',$value['menu_id']);
				$update_data = [
					'menu_value' => $value['menu_value'],
				];
				$rc_ret = $this->db->update($this->_table,$update_data);
				$i += (int)$rc_ret;
			} 

		}
		//更新表中的审核表中的记录状态为审核中，并且将审核人id置为空
		$this->db->where('user_id',$data['user_id'])->where('task_id',$data['task_id']);
		$rt_ret = $this->db->update("partner_minipro_review_task",['status' => 0,'mis_user_id' => 0]);
		//删除后台表中的记录，将表中审核记录删除，进行下次提交审核操作
		//查询审核表的id
		$this->db->where('user_id',$data['user_id'])->where('task_id',$data['task_id']);
		$info = $this->db->getOne('partner_minipro_review_task');
		$this->db->where('review_task_id',$info['id']);
		$admin_ret = $this->db->delete('partner_minipro_task_mis_audit',1); 
		if (0 < $i && $rt_ret && $admin_ret) {
			$this->db->commit();
			return true;
		}
		//回滚
		$this->db->rollback();
		return false;

	}

	/**
	 * 查询用户是否已经添加过审核项，不允许重复添加
	 *
	 * @param	Integer		$user_id		用户id
	 * @param	Integer		$task_id		任务id
	 * @return	Array						用户任务审核关联项
	 */
	public function isAddedReviewedContent($user_id,$task_id) {

		$this->db->where('user_id',$user_id)->where('task_id',$task_id);
		return $this->db->getOne('partner_minipro_review_task');

	}
	
	/**
	 * 获取提交任务审核的审核意见
	 *
	 * @param	Integer		$user_id		用户id
	 * @param	Integer		$task_id		任务id
	 * @return	Array						审核意见和状态的数组
	 */
	public function getReviewedOpinion($user_id,$task_id) {

		$this->db->where('user_id',$user_id)->where('task_id',$task_id);
		return $this->db->getOne('partner_minipro_review_task',['review_opinion','status']);

	}

}//class
