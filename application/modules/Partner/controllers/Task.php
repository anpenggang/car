<?php

/**
 *
 * @name TaskController
 * @desc 任务控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Task.php v0.0 2018/3/5 新建
 */
class TaskController extends BaseController {

	private $_model = null;
	protected $_userinfo = [];
	
	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		
		//实例化任务模型
		$this->_model = new PtnTaskModel();

		//根据sessionID获取用户ID
		$this->_userinfo = $this->verifySessionid($this);

	}

	/**
	 * 可接任务单
	 */
	public function getMyacceptableTaskListAction () {

		if ($this->getRequest()->getMethod() == "GET") {

			$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//第几页
        	$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页数量
        	if (!($page&&$size)) {
            	$page = 1;
            	$size = 12; 
        	}
			//获取已接受任务列表
			$user_task_model = new PtnUserTaskModel();
			$accepted_task = $user_task_model->getUserAcceptedTaskIds($this->_userinfo['user_id']);
			$accepted_task_ids = implode(',',array_column($accepted_task,'task_id'));
			//获取任务列表
			$acceptable_task_item = $this->_model->getAcceptableTaskList($this->_userinfo['user_id'],$accepted_task_ids,$page,$size);
			if (empty($acceptable_task_item)) {
				return Common_Util::returnJson('20002','暂无信息');
			}
			$count = $this->_model->getAcceptableTaskCount($this->_userinfo['user_id'],$accepted_task_ids);
			foreach($acceptable_task_item as $key => $value) {
	
				switch ($value['pay_method']) {
					case 1:
						$acceptable_task_item[$key]['pay_method'] = "审核通过立即结算";
						break;
					case 2:
						$acceptable_task_item[$key]['pay_method'] = "周结（每周三）";	
						break;
					case 3:
						$acceptable_task_item[$key]['pay_method'] = "月结（20号）";
						break;
				}

				$acceptable_task_item[$key]['end_time'] = Common_Util::lessTime(time(),$value['end_time']);
				 
			}
			$data = [
				'item' => array_values($acceptable_task_item),
				'curPage' => $page,
				'curSize' => $size,
				'count' => $count[0]['count'],
		 	];	
			return Common_Util::returnJson('20001','查询成功',$data);
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 已接任务列表(带分页)
	 */
	public function acceptedTaskListAction() {

		if ($this->getRequest()->getMethod() == "GET") {

			$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//第几页
        	$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页数量
        	if (!($page&&$size)) {
            	$page = 1;
            	$size = 12; 
       		}
			$user_task_model = new PtnUserTaskModel();
			$accepted_tasks = $user_task_model->getAcceptedTaskList($this->_userinfo['user_id'],$page,$size);
			//var_dump($accepted_tasks);
			if (empty($accepted_tasks)) {
				return Common_Util::returnJson('20002','暂无数据');
			}
			foreach ($accepted_tasks as $key => $value) {
	
				switch ($value['pay_method']) {
					case 1:
						$accepted_tasks[$key]['pay_method'] = "审核通过立即结算";
						break;
					case 2:
						$accepted_tasks[$key]['pay_method'] = "周结（每周三）";	
						break;
					case 3:
						$accepted_tasks[$key]['pay_method'] = "月结（20号）";
						break;
				}
				switch($value['review_status']) {
					case 0:
					case 1:
						$accepted_tasks[$key]['status_b'] = true;
						break;
					case 2:
						$accepted_tasks[$key]['status_b'] = false;
						break;	
				}
				$accepted_tasks[$key]['end_time'] = Common_Util::lessTime(time(),$value['end_time']);
				 
			}
			$count = $user_task_model->getAcceptedTaskListCount($this->_userinfo['user_id']);
			$data = [
				'item' => $accepted_tasks,
				'curPage' => $page,
				'curSize' => $size,
				'count' => $count[0]['count']
			];
			return Common_Util::returnJson('20001','查询成功',$data,$this->getDataAccess($this));

		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}
	}

	/**
	 * 接单前验证身份
	 */
	public function checkInfoAction() {

			//用户资料完善后才能接单
			$user_model = new PtnUserModel();
			$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);

			//资料未完善不能抢单
			if ($user_info['mobilephone'] == "" 
				|| $user_info['realname'] == '' 
				|| $user_info['school_id'] == '') {
			
				return Common_Util::returnJson('10004','完善资料后才可以抢单');
			}
			$this->tripleForbiddenUser($this);//三次未完成任务，对用户进行封禁	
			//账号被封禁，不能抢单
			if ($user_info['forbidden_endtime'] > time() && $user_info['isforbidden'] === 1) 
				return  Common_Util::returnJson('10011',"账户已被临时封禁，请去认证用户身份，并且等到封禁时间到才能进行操作");
			if ($user_info['isdel'] === 1 || $user_info['isforbidden'] == 2)	{
				return Common_Util::returnJson('10003','账号异常不能接单');
			}

			return Common_Util::returnJson('20001','资料审核完成,可以接单');

	}

	/**
	 * 接单
	 */
	public function addUserTaskAction() {

		if ($this->getRequest()->getMethod() == 'POST') {
			$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');
			
			//用户资料完善后才能接单
			$user_model = new PtnUserModel();
			$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);

			//资料未完善不能抢单
			if ($user_info['mobilephone'] == "" 
				|| $user_info['realname'] == '' 
				|| $user_info['school_id'] == '') {
			
				return Common_Util::returnJson('10004','完善资料后才可以抢单');
			}
			
			//账号被封禁，不能抢单
			if ($user_info['forbidden_endtime'] > time() && $user_info['isforbidden'] === 1 ) 
				return  Common_Util::returnJson('10011',"账户已被临时封禁，请去认证用户身份，并且等到封禁时间到才能进行操作");
			if ($user_info['isdel'] === 1 || $user_info['isforbidden'] == 2)	{
				return Common_Util::returnJson('10003','账号异常不能接单');
			}
			//查询学校信息
			$school_model = new PtnSchoolModel();
			$school_info = $school_model->getLocation($user_info['school_id']);

			$task_info = $this->_model->getTaskInfo($task_id);
			//任务人数限制到达上限之后不允许接单
			if ($this->_model->getAcceptersNum($task_id)['count'] >=  $task_info['executers']) {
				return Common_Util::returnJson('10004','任务已发放完毕');
			}
			//判断活动是否结束,结束后不允许接单
			if ($task_info['end_time'] < time()) {
				return Common_Util::returnJson('10004','任务已经结束');
			}
			//地区不符合要求，不能接单
			switch ($task_info['req_region']) {
				case 0: //不限地区
					break;
				case 1: //限制省份
					if ($school_info['province'] != $task_info['req_province'])
						return Common_Util::returnJson('10010','省份不符合要求，不能接单');
					break;
				case 2: //限制市
					if ($school_info['city_id'] != $task_info['req_city'])
						return Common_Util::returnJson('10010','城市不符合要求，不能接单');
					break;
				case 3: //限制学校
					if ($school_info['id'] != $task_info['req_school'])
						return Common_Util::returnJson('10010','所在学校不符合要求，不能接单');
					break;
			}

			$user_task_model = new PtnUserTaskModel();//用户任务关联模型
			if (!empty($user_task_model->checkAccepted($this->_userinfo['user_id'],$task_id))) {
				return Common_Util::returnJson('20005','重复接单');
			}
			$data = [
				'task_id' => $task_id,
				'user_id' => $this->_userinfo['user_id'],
				'create_time' => time()
			];
			$user_task_id = $user_task_model->addUserTask($data);
			if (is_numeric($user_task_id) && (0 < $user_task_id)) {
				return Common_Util::returnJson('20004','接单成功');
			}
			//var_dump($add_result);
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}
	}

	/**
	 * 任务详情
	 */
	public function taskDetailAction() {
		
		$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');
		$task_detail = $this->_model->getTaskDetail($task_id,$this->_userinfo['user_id']);
		if (empty($task_detail)) {
			return Common_Util::returnJson('20002','暂无数据');
		}
		$task_detail["description"] = html_entity_decode($task_detail["description"]);
		$task_detail['end_timestamp'] = $task_detail['end_time'];
		switch ($task_detail['pay_method']) {
			case 1:
				$task_detail['pay_method'] = "审核通过立即结算";
				break;
			case 2:
				$task_detail['pay_method'] = "周结（每周三）"; 
				break;
			case 3:
				$task_detail['pay_method'] = "月结（20号）";
				break;
		}
		if($task_detail['end_time']-time() < 86400) {

			$task_detail['calc_time'] = true;

		} else {

			$task_detail['calc_time'] = false;
		}
		$task_detail['end_time'] = Common_Util::lessTime(time(),$task_detail['end_time']);
		return (empty($task_detail)) 
			? Common_Util::returnJson('20002','暂无数据')
			: Common_Util::returnJson('20001','查询成功',$task_detail,$this->getDataAccess($this));
	}
	
	/**
	 * 可接任务单
	 */
	public function acceptableTaskListAction () {

		if ($this->getRequest()->getMethod() == "GET") {
			$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//第几页
        	$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页数量

        	if (!($page&&$size)) {
            	$page = 1;
            	$size = 12; 
        	}  
	
			//获取已接受任务列表
			$user_task_model = new PtnUserTaskModel();
			$accepted_task = $user_task_model->getUserAcceptedTaskIds($this->_userinfo['user_id']);
			$accepted_task_ids = implode(',',array_column($accepted_task,'task_id'));
			//var_dump($accepted_task_ids);exit;
		
			//获取任务列表
			$acceptable_task_item = $this->_model->getMyAcceptableTaskList($this->_userinfo['user_id'],$accepted_task_ids,$page,$size);
		
			if (empty($acceptable_task_item)) {
				return Common_Util::returnJson('20002','暂无信息');
			}

			$count = $this->_model->getAcceptableTaskCount($this->_userinfo['user_id'],$accepted_task_ids);
			//print_r($count);exit;
			foreach($acceptable_task_item as $key => $value) {
	
				switch ($value['pay_method']) {
					case 1:
						$acceptable_task_item[$key]['pay_method'] = "审核通过立即结算";
						break;
					case 2:
						$acceptable_task_item[$key]['pay_method'] = "周结（每周三）";	
						break;
					case 3:
						$acceptable_task_item[$key]['pay_method'] = "月结（20号）";
						break;
				}

				$acceptable_task_item[$key]['end_time'] = Common_Util::lessTime(time(),$value['end_time']);
				 
			}

			$user_model = new PtnUserModel();
			$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);
			if ($user_info['school_id'] == 0) {

				$data = [
					'item' => array_values($acceptable_task_item),
					'curPage' => 1,
					'curSize' => count($acceptable_task_item),
					'count' => count($acceptable_task_item),
		 		];	

			} else {

				$data = [
					'item' => array_values($acceptable_task_item),
					'curPage' => $page,
					'curSize' => $size,
					'count' => $count[0]['count'],
		 		];	
			}
			return Common_Util::returnJson('20001','查询成功',$data,$this->getDataAccess($this));
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

}//class
