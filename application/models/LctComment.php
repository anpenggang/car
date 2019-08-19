<?php

/**
 *
 * @name LctComment.php
 * @desc Lecture_comment 评论模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctComment.php v0.0 2018/4/9 新建
 */
class LctCommentModel extends BaseModel {

	private $_table = 'lecture_comment';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 添加课程评论
	 */
	public function addComment($data) {

		return $this->db->insert($this->_table,$data);

	}

	/**
	 * 获取课程评论
	 *
	 * @param	Integer		$course_id	课程id
	 * @return	Array					评论数组 
	 */
	public function getCommentList($course_id) {

		$this->db->where('course_id', $course_id);
		return $this->db->get($this->_table, null,['content']);

	}
	
}//endclass
