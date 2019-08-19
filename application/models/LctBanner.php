<?php

/**
 *
 * @name LctBanner.php
 * @desc Lecture_Banner Banner模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctBanner.php v0.0 2018/4/13 新建
 */
class LctBannerModel extends BaseModel {

	private $_table = 'lecture_banner';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 获取Banner列表
	 */
	public function getBannerList($user_id) {

		$sql = "SELECT b.chief_id,b.imgsrc,uc.pay_method,c.title,MAX(cc.periods) as periods FROM {$this->_table} b";
		$sql .= " LEFT JOIN lecture_user_chief uc ON b.chief_id = uc.chief_id AND uc.user_id = {$user_id}";
		$sql .= " LEFT JOIN lecture_course c ON c.id = b.chief_id";
		$sql .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id = b.chief_id";
		$sql .= " WHERE b.status = 1 AND cc.periods IS NOT NULL";
		$sql .= " GROUP BY cc.chief_id";
		$sql .= " ORDER BY b.weight DESC,b.id DESC";
		return $this->db->rawQuery($sql);
	}

}
