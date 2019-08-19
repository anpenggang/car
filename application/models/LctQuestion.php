<?php

/**
 *
 * @name LctQuestion.php
 * @desc Lecture_Question 提问模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctQuestion.php v0.0 2018/4/9 新建
 */
class LctQuestionModel extends BaseModel {

	private $_table = 'lecture_question';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 添加提问
	 */
	public function addQuestion($data) {

		return $this->db->insert($this->_table,$data);

	}

	/**
	 * 查询FAQ
	 */
	public function questionList($course_id,$page,$size) {

		$limit_start = ($page-1) * $size; 
		$sql = "SELECT question,solution,course_id,user_id FROM {$this->_table}";
		$sql .= " WHERE course_id = {$course_id} AND isdel=2 AND isplay=1";
		$sql .= " ORDER BY id DESC";
		$sql .= " LIMIT {$limit_start},$size";
		return $this->db->rawQuery($sql);

	}

	/**
	 * 查询FAQ总数
	 */
	public function questionCount($course_id) {

		$sql = "SELECT COUNT(1) AS count FROM {$this->_table}";
		$sql .= " WHERE course_id = {$course_id} AND isdel=2 AND isplay=1";
		return $this->db->rawQuery($sql);

	}

}//endclass
