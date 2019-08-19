<?php

/**
 *
 * @name SubscribeController
 * @desc Subscribe控制器，订阅的控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Subscribe.php v0.0 2017/12/19 新建
 */

class SubscribeController extends BaseController {

	private $_model = null;

	/**
	 * 初始化方法，控制器被调用的时候先执行初始化方法,作用域当前控制器
	 */
	public function init() {

		parent::init();//调用父类的初始化方法
		
		$this->_model = new CdpSubscribeModel();

	}
	
	/**
	 * 订阅方法
	 */
	public function addSubscribeAction() {
		
		if ($this->getRequest()->getMethod() == "POST") {
			$args['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n');//预约用户id
			$args['phone'] = Common_Util::getHttpReqQuery($this,'phone','Int','n');//用户预约预留手机号
			$args['school'] = Common_Util::getHttpReqQuery($this,'school','Str','n');//用户预约预留学校
			$args['create_time'] = time();
			//正则验证用户传递过来的手机号码
			if (!preg_match('/^[1][3,4,5,7,8][0-9]{9}$/',$args['phone'])) {
				return Common_Util::returnJson('10004','输入的不是标准的手机号');
			}
			//查看用户如果已经订阅就不能重复订阅
			$issubscribe = $this->_model->listSubscribe($args['user_id']);
			if(!empty($issubscribe)) {
				return Common_Util::returnJson('10004','已经订阅,不能重复');
			}

			$result = $this->_model->addSubscribe($args);
			if(is_numeric($result) && $result > 0) {
				return Common_Util::returnJson('20004','添加订阅信息成功');
			}
			return Common_Util::returnJson('20005','添加订阅信息失败');
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}
	
	}

	/**
	 * 查询订阅信息
	 */
	public function listSubscribeAction() {
		
		if($this->getRequest()->getMethod() == 'GET') {

			$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n');//用户id			
			$result = $this->_model->listSubscribe($user_id);
			if(!empty($result)) {
				return Common_Util::returnJson('20001','查询成功',$result);
			}
			return Common_Util::returnJson('20002','该用户暂时没有订阅');
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}

	}

	/**
	 * 活动预约截止日期判断
	 */
    public function subscribeStatusAction() {
    
        if(time() > strtotime("2017-12-31 23:30:00")) {
            return Common_Util::returnJson('20001','预约已经结束');
        } else {
            return Common_Util::returnJson('20002','预约正在进行');
        }   

    }

	/**
	 * 预约用户详细信息
	 */
	public function subscribeUserInfoAction() {

		Yaf_Dispatcher::getInstance()->enableView();
		$username = Yaf_Session::getInstance()->get("username");
		if($username == NULL) {
			$this->redirect("/index/login");
			return false;
		}

		$page = $this->getRequest()->getQuery("page");
		$size = $this->getRequest()->getQuery("size");

		if(!($page && $size)){
			$page = 1;
			$size = 12;
		}
		
		$subscribeUserInfo = $this->_model->subscribeUserInfo($page,$size);
		$maxNum = $this->_model->subscribeUserInfoCount()[0]['count'];	
		$this->getView()->assign("items",$subscribeUserInfo);
		$this->getView()->assign("maxNum",intval($maxNum));
		$this->getView()->assign("curPage",intval($page));
		$this->getView()->assign("curSize",intval($size));

		return true;		

	}

	/**
	 * 显示院校对应的预约人数
	 */
	public function collegesSubscribesAction() {
		
		Yaf_Dispatcher::getInstance()->enableView();
		$username = Yaf_Session::getInstance()->get("username");
		if($username == NULL) {
			$this->redirect("/index/login");
			return false;
		}
		$page = $this->getRequest()->getQuery("page");
		$size = $this->getRequest()->getQuery("size");

		if(!($page && $size)){
			$page=1;
			$size=12;
		}
		
		$collegesSubscribes = $this->_model->collegesSubscribes($page,$size);
		$maxNum = $this->_model->selectAllNum();

		$this->getView()->assign("items",$collegesSubscribes);
		$this->getView()->assign("maxNum",intval($maxNum));
		$this->getView()->assign("curPage",intval($page));
		$this->getView()->assign("curSize",intval($size));

		return true;		

	}

	/**
	 * 空模板页面
	 */
	public function plainAction() {

		Yaf_Dispatcher::getInstance()->enableView();
		$username = Yaf_Session::getInstance()->get("username");
		if($username == NULL) {
			$this->redirect("/index/login");
			return false;
		}

	}
	
}
