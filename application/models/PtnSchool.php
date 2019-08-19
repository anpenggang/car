<?php

/**
 *
 * @name PtnSchool.php
 * @desc partner_school 学校模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnSchool.php v0.0 2018/3/16 新建
 */
class PtnSchoolModel extends BaseModel {

	private $_school_table = 'stuhui_school_info2';//学校表，里有省份字段
	private $_city_table = 'stuhui_city_info';//城市表


	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 根据学校id查询省份和城市
	 *
	 * @param	Integer		$school_id	学校id
	 * @return	Array		$ret		学校信息数组
	 */
	public function getLocation($school_id) {

		$sql = "SELECT s.id,s.name,s.province,s.city AS city_id,c.city FROM {$this->_school_table} s";
		$sql .= " LEFT JOIN {$this->_city_table} c ON s.city = c.id";
		$sql .= " WHERE s.id={$school_id} LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		return $ret[0];

	}

}//class
