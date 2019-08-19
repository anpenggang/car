<?php

/**
 *
 * @name Feedback.php
 * @desc 意见反馈控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Feedback.php v0.0 2018/4/13 新建
 */
class FeedbackController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
	
		//实例化意见反馈模型
		$this->_model = new LctFeedbackModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);


	}

	/**
	 * 录入意见反馈
	 */
	public function addAction() {

		if ($this->getRequest()->getMethod() == "POST") {
			$data['user_id'] = $this->_userinfo['user_id'];
			$data['content'] = Common_Util::getHttpReqQuery($this,'content','Str','n');//反馈内容
			$data['create_time'] = time();
			$result = $this->_model->addFeedback($data);
			if($result) {
				return Common_Util::returnJson('20001','意见反馈添加成功');
			}
			return Common_Util::returnJson('20004','意见反馈添加失败');
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}
	}

}//endclass
