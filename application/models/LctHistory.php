<?php

/**
 *
 * @name LctHistory.php
 * @desc Lecture_history 用户访问历史模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctHistory v0.0 2018/4/12 新建
 */
class LctHistoryModel extends BaseModel {

	private $_table = 'lecture_history';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 新增一条用户访问记录
	 */
	public function addHistory($data) {

		return $this->db->insert($this->_table,$data);

	}
	
	/**
	 * 根据用户id和课程id判断表中是否已经有记录
	 */
	public function isAdded ($user_id, $course_id) {

		$this->db->where('user_id', $user_id)->where('course_id', $course_id);
		return $this->db->getOne($this->_table,['id']);
		
	}

}
