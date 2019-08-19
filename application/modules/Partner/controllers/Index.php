<?php

/**
 *
 * @name IndexController
 * @desc 首页控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Index.php v0.0 2018/2/26 新建
 */
class IndexController extends BaseController {

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
	 * 首页方法
	 */
	public function indexAction() {

		return Common_Util::returnJson('20001','请求成功，返回首页信息');

	}

	/**
	 * 给客户发送消息
	 */
	public function sendAction($formId) {

		$user_model = new PtnUserModel();
		$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);
		$task_name = "小程序测试任务";
		$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$this->getAccessToken();
		$json_data = '{
			"touser":"'.$user_info['openid'].'",
			"template_id":"XHlLygz123JFuzolKJW2-8FaeZYpN0XJ323kDaybRRs",
			"page": "index",
			"form_id": "'.$formId.'",
			"data": {
				"keyword1": {
					"value": "'.$task_name.'", 
					"color": "#173121"
				}, 
				"keyword2": {
          			"value": "2018年3月30日 15:10:00", 
          			"color": "#173177"
      			}, 
      			"keyword3": {
          			"value": "你的任务'.$task_name.'即将延期，请及时处理!", 
          			"color": "#173177"
      			}, 
      			"keyword4": {
          			"value": "任务即将截止", 
          			"color": "#173177"
      			} 
  			},
  			"emphasis_keyword": "keyword4.DATA" 
		}';
		print_r($this->jsonHttp($url,$json_data));

	}

}//class
