<?php

/**
 *
 * @name AdviseController
 * @desc 建议控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Advise.php v0.0 2018/3/8 新建
 */
class AdviseController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化建议类模型
		$this->_model = new PtnAdviseModel();

		//根据sessionID获取用户ID
		$this->_userinfo = $this->verifySessionid($this);

	}

	/**
	 * 添加建议方法
	 */
	public function addAction() {

		if ($this->getRequest()->getMethod() == 'POST') {

			$content = Common_Util::getHttpReqQuery($this,'content','Str','n');
			$type = Common_Util::getHttpReqQuery($this,'type','Int','y',0);
			$data = [
				'user_id' => $this->_userinfo['user_id'],
				'type' => $type,
				'content' => $content,
				'create_time' => time()
			];
			$add_ret = $this->_model->add($data);
			if (is_numeric($add_ret) && 0 < $add_ret) {
				return Common_Util::returnJson('20004','添加建议信息成功');
			}
			return Common_Util::returnJson('20005','添加建议信息失败，请重试');
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

}//class
