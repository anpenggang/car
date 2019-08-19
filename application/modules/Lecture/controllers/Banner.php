<?php

/**
 *
 * @name BannerController
 * @desc Banner控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Banner.php v0.0 2018/4/13 新建
 * @version 	       v1.0 2018/5/16 重写此控制器 fuck
 */
class BannerController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {
		
		//调用父类的初始化方法
		parent::init();
		
		//实例化课程模型
		$this->_model = new LctBannerModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
	
	}

	/**
	 * 获取banner
	 */
	public function listAction() {

		$ret = $this->_model->getBannerList($this->_userinfo['user_id']);
		if (empty($ret)) {
			return Common_Util::returnJson('20002', '暂无数据');
		}
		foreach ($ret as $key => $value) {
			if ($value['pay_method'] === null) {//未购买
				$ret[$key]['pay_status'] = '未支付';
				unset($ret[$key]['isshare']);
			} else {
				$ret[$key]['pay_status'] = '已支付';
			}
			unset($ret[$key]['pay_method']);
		}
		return Common_Util::returnJson('20001','查询成功',$ret);

	}

}//endclass
