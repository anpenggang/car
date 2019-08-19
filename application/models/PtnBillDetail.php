<?php

/**
 *
 * @name PtnBillDetail.php
 * @desc partner_bill_detail 建议模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnAdvise.php v0.0 2018/3/8 新建
 */
class PtnBillDetailModel extends BaseModel {

	private $_table = 'partner_minipro_bill_detail';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 获取用户邀请其他用户的佣金所得
	 */
	public function  getInviteIncome($user_id) {

		$sql = "SELECT IFNULL(SUM(money),0) AS income FROM {$this->_table} WHERE user_id = {$user_id} AND type=2";
		$ret = $this->db->rawQuery($sql);
		return $ret[0];

	}

}
