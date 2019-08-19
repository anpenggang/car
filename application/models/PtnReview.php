<?php

/**
 *
 * @name PtnReview.php
 * @desc partner_review 审核模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnReview.php v0.0 2018/3/6 新建
 */
class PtnReviewModel extends BaseModel {

	private $_table = 'partner_minipro_review_task';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 根据不同状态，返回审核详情
	 *
	 * @param	Integer		$user_id	用户id
	 * @param	Integer		$status		审核状态
	 * @param	Integer		$page		页码
	 * @param	Integer		$size		每页显示的数量
	 * @return	Array		$ret		返回收益详情
	 */	
	public function getReviewByStatus($user_id, $pay_status,$page,$size=20) {
		//判断status
		if($pay_status==2){
			$status = 2;
			$pay_money_status = 2;
		}elseif($pay_status==1){
			$status = 2;
			$pay_money_status = 1;
		}
		$sql = "select count(*) as total from $this->_table where user_id=$user_id and status=$status and pay_money_status=$pay_money_status";
		$number = $this->db->rawQuery($sql);
		$pageUtil = new Page($number[0]['total'],$size);
		if($pageUtil->pagenum < $page){
			return false; 
		}
		$start = ($pageUtil->page-1)*($pageUtil->pagesize);
		$sql = "select a.name,a.reward,b.pay_money_date,a.end_time,b.status,a.pay_method,a.id as task_id from partner_minipro_task a inner join $this->_table b  on a.id=b.task_id where b.user_id=$user_id and b.status = $status and b.pay_money_status=$pay_money_status order by a.create_time desc limit $start,$size";
		$ret = $this->db->rawQuery($sql);
		if(!empty($ret)){
			$data=[];
			foreach($ret as $k=>$v){
				if($pay_status==1){
					$data[$k]['etime'] = date('Y-m-d H:i:s',strtotime($v['pay_money_date']));
				}	
				$data[$k]['name'] = $v['name'];
				$data[$k]['reward'] = $v['reward'];
				if($v['pay_method']==1){
					$data[$k]['pay_method'] = '审核通过立即结算';
				}elseif($v['pay_method']==2){
					$data[$k]['pay_method'] = '周结(每周三)';
				}elseif($v['pay_method']==3){
					$data[$k]['pay_method'] = '月结(20号)';	
				}
				if($v['status']==1){
					$data[$k]['task_id'] = $v['task_id'];
				}
			}
			
		}
		return empty($data)?false:$data;
	}
}//class
