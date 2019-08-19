<?php

/**
 *
 * @name PtnReviewMenu
 * @desc partner_review_menu 审核项模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version partner_review_menu.php v0.0 2018/3/6 新建
 */
class PtnReviewMenuModel extends BaseModel {

	private $_table = 'partner_minipro_review_menu'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() { 
		
		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 获取指定任务审核项
	 *
	 * @param	Interger	$task_id	任务id
	 * @return	Array		$ret		审核项内容
	 */
	public function getReviewMenu($task_id) {

		$this->db->where('task_id',$task_id);
		$ret = $this->db->get($this->_table,null,['id','name','type','intro']);
		return $ret;

	}
}
