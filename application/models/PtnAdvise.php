<?php

/**
 *
 * @name PtnAdvise.php
 * @desc partner_advise 建议模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnAdvise.php v0.0 2018/3/8 新建
 */
class PtnAdviseModel extends BaseModel {

	private $_table = 'partner_minipro_advise';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}
	
	/**
	 * 添加建议方法
	 *
	 * @param	Array		$data		用户id
	 * @return	Mixed		$ret		成功返回新增id，失败返回false
	 */
	public function add($data) {

		$ret = $this->db->insert($this->_table,$data);
		return $ret;

	}

}//class
