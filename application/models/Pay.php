<?php

/**
 *
 * @name ActivityModel
 * @desc 活动信息模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Activity.php v0.0 2017/10/9 新建
 */
class PayModel extends BaseModel {

	private $_table = 'barrage_payment'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 支付成功向数据表里面写入一条支付记录
	 *
	 * @param	Array	$data	需要写入的记录
	 * @return	Mixed	$ret	成功返回新增的id，失败返回false
	 */
	public function addPayRecorder($data) {
		$ret = $this->db->insert($this->_table,$data);
		return $ret;
	}

	/**
	 * 查询订单金额
	 *
	 * @param	String	$out_trade_no	订单号
	 * @return	Array	$ret			订单号所对应一条记录
	 */
	public function getPayRecordByBillNumber($out_trade_no) {
	
		$this->db->where('out_trade_no',$out_trade_no);
		$ret = $this->db->getOne($this->_table);
		return $ret;

	}

	/**
	 * 更新订单信息状态
	 *
	 * @param	String	$out_trade_no	订单号
	 * @return	Boolean	$ret			成功返回true，失败返回false
	 */
	public function updateOrderStatus($out_trade_no) {

		$this->db->where('out_trade_no',$out_trade_no);
		$ret = $this->db->update($this->_table,['order_status'=>1]);
		return $ret;

	}	

}
