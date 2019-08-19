<?php

/**
 *
 * @name ChiefController
 * @desc 课程控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Course.php v1.0 2018/5/11 新建
 */
class ChiefController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {
		
		//调用父类的初始化方法
		parent::init();
		
		//实例化课程模型
		$this->_model = new LctChiefModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
	
	}

	/**
	 * 主课程列表页(包括精品和专栏课程)
	 */	
	public function listAction() {

		$page = Common_Util::getHttpReqQuery($this,'page','Int','n');//第几页
		$size = Common_Util::getHttpReqQuery($this,'size','Int','n');//每页显示数目
		$chief_list = $this->_model->getChiefList($this->_userinfo['user_id'], $page, $size);

		if (empty($chief_list)) {
			return Common_Util::returnJson('20002', '暂无数据');
		}
		foreach ($chief_list as $key => $value) {
			if ($value['pay_method'] === null) {
				$chief_list[$key]['pay_status'] = '未支付';
				unset($chief_list[$key]['isshare']);
			} else {
				$chief_list[$key]['pay_status'] = '已支付';
			}
			if ($value['type'] == 1) {
				$chief_list[$key]['type'] = '专栏';
			} else {
				$chief_list[$key]['type'] = '精品';
			}
			
			$chief_list[$key]['periods_start_time'] = date('m月d日',$value['periods_start_time']);
			unset($chief_list[$key]['pay_method']);
			$chief_list[$key]['fans'] = $this->_model->getChiefUserNum($value['chief_id'],$value['periods']);
			//$chief_list[$key]['limit_num'] = $this->_model->getLimitNum($value['chief_id'],$value['periods']);
			//$chief_list[$key]['span_periods'] = $this->_model->getSpanPeriods($value['chief_id'],$value['periods']);
		}
		$count = $this->_model->getChiefCount($this->_userinfo['user_id'])[0]['count'];
		$ret = [
			'curPage' => $page,
			'curSize' => $size,
			'count' => $count,
			'item' => $chief_list,
		];

		return Common_Util::returnJson('20001', '查询成功', $ret);
	}

	/**
	 * 主课程详情页
	 */
	public function detailAction() {

		$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n'); //主课程ID
		$periods = Common_Util::getHttpReqQuery($this,'periods','Int','n'); //主课程ID
		$chief_detail = $this->_model->getChiefDetail($chief_id, $this->_userinfo['user_id'], $periods);
		if (empty($chief_detail)) {
			return Common_Util::returnJson('20002','暂无数据');
		}
		foreach ($chief_detail as $key => $value) {
			if ($value['pay_method'] === null) {
				$chief_detail[$key]['pay_status'] = '未支付';
				unset($chief_detail[$key]['pay_method']);
			} else {
				$chief_detail[$key]['pay_status'] = '已支付';
				($value['pay_method'] === 1)
					? $chief_detail[$key]['pay_method'] = '分享购买'
					: $chief_detail[$key]['pay_method'] = '直接购买';
			}   
			if ($value['type'] == 1) {
				$chief_detail[$key]['type'] = '专栏';
				$chief_detail[$key]['periods_start_time'] = $this->lessTime(time(),$value['periods_start_time']);
            } else {
                $chief_detail[$key]['type'] = '精品';
				$chief_detail[$key]['periods_start_time'] = '';
            }
			$chief_detail[$key]['fans'] = $this->_model->getChiefUserNum($value['chief_id'],$value['periods']);
			$chief_detail[$key]['limit_num'] = $this->_model->getLimitNum($value['chief_id'],$value['periods']);
		}
		//获取已学习用户头像
		$result = $chief_detail[0];
		$result['user_list'] = $this->_model->getChiefUser($chief_id,$periods);
		$related_list = $this->_model->getChiefRecommend($chief_id, $this->_userinfo['user_id']);
		if (!empty($related_list)) {
			foreach ($related_list as $key => $value) {
				if ($value['pay_method'] === null) {
					$related_list[$key]['pay_status'] = '未支付';//未付款
				} else {
					$related_list[$key]['pay_status'] = '已支付';
				}   
				unset($related_list[$key]['pay_method']);
				$related_list[$key]['fans'] = $this->_model->getChiefUserNum($chief_id, $periods);
			} 
		}
		$result['related_list'] = $related_list;
		
		return Common_Util::returnJson('20001','查询成功',$result);

	}

    /** 
     * 获取用户购买列表
     */
    public function purchasedAction() {

        $page = Common_Util::getHttpReqQuery($this,'page','Int','n');//第几页
        $size = Common_Util::getHttpReqQuery($this,'size','Int','n');//每页数量
        if (!($page && $size)) {
            $page = 1;
            $size = 3;  
        }   
        $purchase_list = $this->_model->getpurchased($this->_userinfo['user_id'], $page, $size);
        $count = $this->_model->getpurchasedCount($this->_userinfo['user_id'])[0]['count'];
        $recommend_list = $this->_model->getPurchasedRecommend($this->_userinfo['user_id']);
        if (!empty($purchase_list)) {
			foreach ($purchase_list as $key => $value) {
            	$purchase_list[$key]['fans'] = $this->_model->getChiefUserNum($value['chief_id'], $value['periods']);
				$value['type'] === 1 
					? $purchase_list[$key]['type'] = '专栏'
					: $purchase_list[$key]['type'] = '精品';
				$purchase_list[$key]['periods_start_time'] = date('m月d日',$value['periods_start_time']);
        	}
		}
		if (!empty($recommend_list)) { 
        	foreach ($recommend_list as $key => $value) {
            	$recommend_list[$key]['fans'] = $this->_model->getChiefUserNum($value['chief_id'], $value['periods']);
            	$recommend_list[$key]['pay_method'] = '未支付';
        	}
		}
        $ret = [ 
            'curPage' => $page,
            'curSize' => $size,
            'count' => $count,
            'purchase_list' => $purchase_list,
            'recommend_list' => $recommend_list
        ];  
    
        return Common_Util::returnJson('20001', '查询成功', $ret);

    }   

	/**
	 * 设置课程状态为已分享
	 */
	public function sharingAction() {

		if ($this->getRequest()->getMethod() == "POST") {
			$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n');//课程id
            $ret = $this->_model->updateUserChief($chief_id,$this->_userinfo['user_id'],['isshare' => 1,'update_time'=>time()]);
            if ($ret) {
                return Common_Util::returnJson('20001','修改状态成功');
            } else {
                return Common_Util::returnJson('20006','修改状态失败');
            }
        } else {
            return Common_Util::returnJson('10007','请求方法有误');
        }

    }

	/** 
	 * 计算任务剩余时间
	 */
	private function lessTime($time_s,$time_n){

		$strtime = ''; 
		$time = $time_n-$time_s;
		if ($time >= 86400*365) {
            return $strtime = "永久有效";
        }   
        if($time >= 86400){
            return $strtime = floor($time/86400)."天";
        }   
        if($time >= 3600){
            $strtime .= str_pad(intval($time/3600),2,'0',STR_PAD_LEFT).':';
            $time = $time % 3600;
        }else{
            $strtime .= '00'.':';
        }   
        if($time >= 60){
            $strtime .= str_pad(intval($time/60),2,'0',STR_PAD_LEFT).':';
            $time = $time % 60; 
        }else{
            $strtime .= '00'.':';
        }   
        if($time >= 0){ 
            $strtime .= str_pad(intval($time),2,'0',STR_PAD_LEFT);
        }else{
            $strtime = "已开课";
        }   
        return $strtime;

    }

}//endclass	
