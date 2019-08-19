<?php

/**
 * @name LctFeedback.php
 * @desc lecture_feedback 用户意见反馈模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctFeedback.php v0.0 2018/4/9 新建
 */
class LctFeedbackModel extends BaseModel {

	private $_table = 'lecture_feedback';

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 新增意见反馈
	 *
	 * @param 	Array	$data	新增数据数组
	 * @return	Mixed	$ret	成功时返回新增id失败返回false
	 */
	public function addFeedback($data) {

		$ret = $this->db->insert($this->_table,$data);
		return $ret;

	}

}
