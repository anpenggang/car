<?php

/**
 *
 * @name NoticeController
 * @desc 通知控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Notice.php v0.0 2018/3/7 新建
 */
class NoticeController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化通知模型
		$this->_model = new PtnNoticeModel();

		//根据sessionID获取用户ID
		$this->_userinfo = $this->verifySessionid($this);

	}

	/**
	 * 获取用户通知
	 */
	public function getNoticeAction() {

		$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//页码
		$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页显示数量

		if (!($page&&$size)) {
			$page = 1;
			$size = 12;
		}
		
		$notice_list = $this->_model->getNotice($this->_userinfo['user_id'],$page,$size);
		if (empty($notice_list)) {
			return Common_Util::returnJson('20002','暂无通知消息');
		}

		foreach ($notice_list as $key => $value) {
			
			$notice_list[$key]['publish_time'] = date('Y-m-d H:i:s',$value['publish_time']);
		
		}
		$count = $this->_model->getNoticeCount($this->_userinfo['user_id']);
		$data = [
			'item' => $notice_list,
			'curPage' => $page,
			'curSize' => $size,
			'count' => $count[0]['count']
		];
		$this->readedAllNotice();
		return Common_Util::returnJson('20001','查询成功',$data);

	}

	/**
	 * 获取系统消息的数量
	 */
	public function getNoticeCountAction() {

		$count = $this->_model->getNoticeCount($this->_userinfo['user_id']);
		if (empty($count)) {	
			return Common_Util::returnJson('20002','暂无系统消息');
		}
		
		return Common_Util::returnJson('20001','查询成功',$count[0]);	
	}

	/**
	 * 用户所有未读消息置为已读消息
	 */
	private function readedAllNotice() {
		
		$user_id = $this->_userinfo['user_id'];
		$ret = $this->_model->readedAllNotice($user_id);

	}

	/**
	 * 用户点击阅读消息，将其状态设置为已读
	 */
	public function readedNoticeAction() {
		
		if ($this->getRequest()->getMethod() == "POST") {
			$notice_id = Common_Util::getHttpReqQuery($this,'notice_id','Int','n');		
			//判断通知状态是否为已阅读
			$isreaded = $this->_model->isReadedNotice($this->_userinfo['user_id'],$notice_id);
			if (!empty($isreaded)) {
				return ($isreaded['isread'] === 1)
					? Common_Util::returnJson('10004','已经为阅读状态无需处理')
					: (($this->_model->setToReaded($this->_userinfo('user_id'),$notice_id))
						? Common_Util::returnJson('20006','添加已阅读状态成功')
						: Common_Util::returnJson('20007','数据更新失败，请重试'));
			}

			//关联表中无记录的话新增一条记录，并将其状态设置为已读
			$add_readed_notice = $this->_model->readedNotice($this->_userinfo['user_id'],$notice_id);
			return (is_numeric($add_readed_notice) && 0 < $add_readed_notice)
				? Common_Util::returnJson('20004','添加已阅读状态成功')
				: Common_Util::returnJson('20005','添加已阅读状态失败');
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 删除系统消息
	 */	
	public function delNoticeAction() {

		$notice_id = Common_Util::getHttpReqQuery($this,'notice_id','Int','n');
		$del_res = $this->_model->delNotice($this->_userinfo['user_id'],$notice_id);
		return ($del_res) 
			? Common_Util::returnJson('20006','修改状态成功')
			: Common_Util::returnJson('20007','修改状态失败');
								
	}

	/**
	 * 保存触发表单时间时传递的formId
	 */
	public function addFormIdsAction() {

		//echo json_encode($this->getFormIds('oZ6R_4nJKFVYdufszxtC6RED9BKs'));exit;
		$formId = Common_Util::getHttpReqQuery($this,'formId','Str','n');;//formId，用来给用户发送模板消息
		$user_model = new PtnUserModel();
		$openId = $user_model->getUserInfo($this->_userinfo['user_id'])['openid'];
		$catch = [
				'formId' => $formId,
				'expiry_time' => time() + 86400 * 7
			];
		$arr = [$catch];
		$res = $this->getFormIds($openId);
		$cache_result = false;
		if($res){
			$new = array_merge($res, $arr);//合并数组
			if ($this->saveFormIds($openId,$new)) {
				//error_log(print_r($this->getFormIds($openId),true));
				$cache_result = true;
			}
		}else{
			$result = $arr;
			if ($this->saveFormIds($openId,$result)) {
				error_log(print_r($this->getFormIds($openId),true));
				$cache_result = true;
			}
		}
		
		if ($cache_result) {
			return Common_Util::returnJson('20001','formId保存成功');
		}
		return Common_Util::returnJson('20002','formId保存失败');

	}

	/**
	 * 获取已经存贮在redis的formId
	 */
	private function getFormIds($openId){

		$cacheKey = md5('user_formId'.$openId);
    	$data = $this->_redis->get($cacheKey);
		if($data)return json_decode($data,TRUE);
		else return FALSE;

	}

	/**
	 * 保存formid到redis中
	 */
	private function saveFormIds($openId,$data){

		$cacheKey = md5('user_formId'.$openId);
		return $this->_redis->setex($cacheKey,60*60*24*7,json_encode($data));

	}
	
}//class
