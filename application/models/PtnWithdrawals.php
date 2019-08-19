<?php

/**
 *
 * @name PtnNotice.php
 * @desc partner_notice 通知模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnNotice.php v0.0 2018/3/7 新建
 */

class PtnWithdrawalsModel extends BaseModel {


	private $_table = 'partner_minipro_withdrawals';
	private $_bill_table = 'partner_minipro_user_bill';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();
	}

	/**
	 * 获取用户账单金额
	 * @param	Integer		$user_id		用户id
	 * @return	Array		$ret			用户账单详情
	 */
	public function getAccount($user_id) {

		$this->db->where('user_id',$user_id);
		$ret = $this->db->getOne($this->_bill_table,['sum_money']);
		return $ret;

	}

	/**
	 * 提现流程
	 *
	 * @param	Integer		$user_id		用户id
	 * @param	Float		$money			提现金额
	 * @param	Float		$account_money	账户总余额
	 * @return	Boolean		$ret			成功返回true，失败返回false
	 */ 
	public function withdrawals($user_id,$money,$account_money) {

		//开启事务
        $this->db->autocommit(false);

		//向申请提现表中写入一条记录
		$data = [
			'user_id' => $user_id,
			'cash' => $money,
			'create_time' => time()
		];
		$detail = $this->db->insert($this->_table,$data);

		//总的金额表中减去提现的金额
		$this->db->where('user_id',$user_id);
        $dec_ret = $this->db->update('partner_minipro_user_bill',['sum_money' => $account_money-$money]);
      
        //提交事务 如果提现表和总金额表操作都成功,提交事务
        if (is_numeric($detail) && (0 < $detail) &&  $dec_ret ) {
            $this->db->commit();
			return true;
        }   
        //回滚
        $this->db->rollback();
        return false;

	}

	/**
	 * 根据id查询对应的提现信息
	 * 
	 * @param	Integer		$withdrawals_id	提现申请所对应的id
	 * @return	Array		$ret			提现信息数组
	 */
	public function getWithdrawalsInfo($withdrawals_id) {

		$this->db->where('id',$withdrawals_id);
		$ret = $this->db->getOne($this->_table);
		return $ret;

	}

	/**
	 * 提现成功之后的回调
	 * @param	Integer		$withdrawals_id		提现申请所对应的id
	 * @param	Array		$data				需要更新的数据
	 * @return	Boolean		$ret				成功返回true，失败返回false
	 */
	public function wxNotify($withdrawals_id,$data) {

		
		$this->db->where('id',$withdrawals_id);
		$ret = $this->db->update($this->_table,$data);
		if ($data['status'] == 4) {
			$withdrawals_info = $this->getWithdrawalsInfo($withdrawals_id);
			$insert_data = [
				'user_id' => $withdrawals_info['user_id'],
				'money' => $withdrawals_info['cash'],
				'type' => 3,
				'obj_id' => $withdrawals_info['id'],
				'create_time' => time()
			];
			$this->db->insert('partner_minipro_bill_detail', $insert_data);
		}
		return $ret;
	}

}
