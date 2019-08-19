<?php

/**
 * @name LctPayment.php
 * @desc Lecture_payment 课程支付模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctPayment.php v0.0 2018/4/13 新建
 */
class LctPaymentModel extends BaseModel {

	private $_table = 'lecture_order';

	/**
	 * 构造方法，调用BaseModel的构造方法，获取数据库连接实例
	 */
	public function __construct() {

		parent::__construct();

	}

	/**
	 * 新增一条支付记录
	 */
	public function addPayRecorder($data) {

		return $this->db->insert($this->_table,$data);

	}

	/**
	 * 根据订单号获取订单详情
	 */
	public function getPayRecordByBillNumber($out_trade_no) {

		$this->db->where('out_trade_no', $out_trade_no);
		return $this->db->getOne($this->_table);

	}

	/**
	 * 支付成功之后的回调
	 */
	public function updateOrderStatus($post_data) {

        //开启事务
        $this->db->autocommit(false);
		$out_trade_no = $post_data['out_trade_no'];

		//设置订单的状态为支付成功
		$this->db->where('out_trade_no',$out_trade_no);
		$order_status_update = $this->db->update($this->_table,[
			'order_status' => 9,
			'transaction_id' => $post_data['transaction_id'],
			'bank_type' => $post_data['bank_type'],
			'pay_time' => $post_data['time_end']
			]);
		$insert_ret = false;
		if ($order_status_update) {
			$payment_info = $this->getPayRecordByBillNumber($out_trade_no);
			$data = [
				'user_id' => $payment_info['user_id'],
				'chief_id' => $payment_info['chief_id'],
				'periods' => $payment_info['periods'],
				'pay_method' => $payment_info['pay_method'],
				'create_time' => time()
			];
			$insert_ret = $this->db->insert('lecture_user_chief', $data);
		}
        if (is_numeric($insert_ret) && (0 < $insert_ret) && $order_status_update) {
            $this->db->commit();
			return true;
        }  else { 
        	//回滚
        	$this->db->rollback();
			return false;
		}

	}

}
