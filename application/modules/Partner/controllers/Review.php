<?php

/**
 *
 * @name ReviewController
 * @desc 审核控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Review.php v0.0 2018/3/6 新建
 */
class ReviewController extends BaseController {

	private $_model = null;	
	protected $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化审核模型
		$this->_model = new PtnReviewModel();

		//根据sessionID获取用户ID
        $this->_userinfo = $this->verifySessionid($this);

	}
	
	/**
	 * 获取审核菜单
	 */
	public function getReviewMenuAction() {
		
		if ($this->getRequest()->getMethod() == "GET") {
			$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');//任务id
			//$review_menu_model = new PtnReviewMenuModel();
			//$menu = $review_menu_model->getReviewMenu($task_id);
			$review_content_model = new PtnReviewContentModel();
			//用户提交的审核项内容和审核菜单一起返回
			$reviewed_content = $review_content_model->getReviewedContent($this->_userinfo['user_id'],$task_id);
			foreach ($reviewed_content as $key => $value) {
				$reviewed_content[$key]['uploaded'] = true;
			}
			if (empty($reviewed_content)) {
				return Common_Util::returnJson('20002','暂无数据');
			}
			return Common_Util::returnJson('20001','查询成功',$reviewed_content);
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 提交审核信息
	 */
	public function addReviewingContentAction() {

		if ($this->getRequest()->getMethod() == "POST") {
			$review_content_model = new PtnReviewContentModel();
			$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');//任务id
			$menu_item = Common_Util::getHttpReqQuery($this,'menu_item','Str','n');//任务id
		
			//判断活动是否结束，结束后不让提交审核内容
			$task_model = new PtnTaskModel();
			$task_info = $task_model->getTaskInfo($task_id);			
			if ($task_info['end_time'] < time()) {
				return Common_Util::returnJson('10004','任务已经结束，不能提交');
			}

			//判断用户是否已经接单
			$user_task_model = new PtnUserTaskModel();
			if (empty($user_task_model->checkAccepted($this->_userinfo['user_id'],$task_id))) {
				return Common_Util::returnJson('10004','未接单,不能添加');
			}		

			//判断用户是否已经添加过审核
			$isadded = $review_content_model->isAddedReviewedContent($this->_userinfo['user_id'],$task_id);
			if (!empty($isadded)) {
				return Common_Util::returnJson('20005','重复提交');
			}

			$menu_item = json_decode(html_entity_decode($menu_item),true);
			//print_r($menu_item);exit;
			$data = [
				'task_id' => $task_id,
				'user_id' => $this->_userinfo['user_id'],
				'menu_item' => $menu_item
			];
			$review_result = $review_content_model->addReviewContent($data);
			if ($review_result) {
				return Common_Util::returnJson('20004','提交成功');
			}
			return Common_Util::returnJson('20005','提交失败，请重试');
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}
		
	}

	/**
	 * 获取审核意见
	 */
	public function getReviewedOpinionAction() {

		$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');//任务id	
		$review_content_model = new PtnReviewContentModel();
		//系统返回的审核意见
		$reviewed_opinion = $review_content_model->getReviewedOpinion($this->_userinfo['user_id'],$task_id);
		if (empty($reviewed_opinion)) {
			return Common_Util::returnJson('20002','暂无未审核意见');
		}
		return Common_Util::returnJson('20001','查询成功',$reviewed_opinion);

	}
	
	/**
	 * 修改审核项	
	 */
	public function updateReviewedContentAction() {

		$review_content_model = new PtnReviewContentModel();
		$task_id = Common_Util::getHttpReqQuery($this,'task_id','Int','n');//任务id

		if ($this->getRequest()->getMethod() == "GET") {
			//用户提交的审核项内容
			$reviewed_content = $review_content_model->getReviewedContent($this->_userinfo['user_id'],$task_id);
			return empty($reviewed_content) 
					? Common_Util::returnJson('20002','暂无数据')
					: Common_Util::returnJson('20001','查询成功',$reviewed_content);

		} else if ($this->getRequest()->getMethod() == "POST") {
			$menu_item = Common_Util::getHttpReqQuery($this,'menu_item','Str','n');//任务id

			//根据任务id获取到任务审核状态，审核不通过才允许修改
			$review_task_info = $review_content_model->getReviewedOpinion($this->_userinfo['user_id'],$task_id);
			if ($review_task_info['status'] !== 1) {
				return Common_Util::returnJson('10004','任务审核中或审核完成，不能修改');
			}
			$menu_item = json_decode(html_entity_decode($menu_item),true);
			$data = [
				'task_id' => $task_id,
				'user_id' => $this->_userinfo['user_id'],
				'menu_item' => $menu_item
			];
			$review_result = $review_content_model->updateReviewContent($data);
			if ($review_result) {
				return Common_Util::returnJson('20004','修改成功');
			}
			return Common_Util::returnJson('20005','修改失败，请重试');
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}

	}
	
	/**
	 * 任务收益明细
	 */
	public function profileDetailAction() {

		$status = Common_Util::getHttpReqQuery($this,'status','Int','n');//审核状态(0:审核中1:审核未通过2:审核已通过)
		$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//页码
		$size = Common_Util::getHttpReqQuery($this,'size','Int','y',20);//每页显示的数量
		$profileDetail = $this->_model->getReviewByStatus($this->_userinfo['user_id'],$status,$page,$size);
		if($profileDetail){
			return Common_Util::returnJson('10004','查询成功',$profileDetail);
		} else {
		//$arr = array(1=>'haha',2=>'puhu');
			return Common_Util::returnJson('10002','无数据');
		}

	}

}//class
