<?php

/**
 *
 * @name CourseController
 * @desc 课程控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Course.php v0.0 2018/4/9 新建
 * @version 		   v1.0 2018/5/15 重写此控制器下所有方法 fuck PM
 */
class CourseController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {
		
		//调用父类的初始化方法
		parent::init();
		
		//实例化课程模型
		$this->_model = new LctCourseModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
	
	}
	
	/**
	 * 获取课程(带分页)
	 */	
	public function listAction() {
				
		$page = Common_Util::getHttpReqQuery($this,'page','Int','n');//第几页
        $size = Common_Util::getHttpReqQuery($this,'size','Int','n');//每页数量
		$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n'); //主课程ID
		$periods = Common_Util::getHttpReqQuery($this,'periods','Int','n'); //主课程期数

        if (!($page && $size)) {
            $page = 1;
            $size = 12; 
        }
  
		$course_list = $this->_model->getCourseList($page,$size,$chief_id,$periods,$this->_userinfo['user_id']);
		if (empty($course_list)) { 
			return Common_Util::returnJson('20002','暂无数据');
		}
		$chief_model = new LctChiefModel();

		foreach ($course_list as $key => $value) {
			if ($value['pay_method'] === null) {
				$course_list[$key]['pay_status'] = '未支付';
				unset($course_list[$key]['isshare']);
			} else {
				$course_list[$key]['pay_status'] = '已支付';
			}
			unset($course_list[$key]['pay_method']);
			$course_list[$key]['start_time'] = date('Y-m-d h:i:s',$value['start_time']);
			$course_list[$key]['speak'] = $chief_model->getChiefInfoById($chief_id)['speaker'];
			$course_list[$key]['fans'] = $chief_model->getChiefUserNum($chief_id,$periods);
		}

		$count = $this->_model->getCourseCount($chief_id,$periods)[0]['count'];
		$data = [ 
			'item' => $course_list,
			'curPage' => $page,
			'curSize' => $size,
			'count' => $count
		]; 
		return Common_Util::returnJson('20001','查询成功',$data);

	}

	/** 
	 * 用户历史统计，写入日志方法
	 *
	 * @user_id 	integer 	用户id
	 * @course_id 	integer 	课程id
	 * return void
	 */
	private function userHistoryLog ($user_id,$course_id) {

		$history_model = new LctHistoryModel();
		if (empty($history_model->isAdded($user_id, $course_id))) return;
		$data = [
			'user_id' => $user_id,
			'course_id' => $course_id,
			'create_time' => time()
		];
		$history_model->addHistory($data);		

	}  

	/**
	 * 获取课程
	 */
	public function detailAction() {

		$course_id = Common_Util::getHttpReqQuery($this,'course_id','Int','n');//子课程ID
		$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n');//主课程ID
		$periods = Common_Util::getHttpReqQuery($this,'periods','Int','n');//期数
		$course_info = $this->_model->detailCourse($course_id, $this->_userinfo['user_id'],$chief_id,$periods);
		if (!empty($course_info)) {
			$course_info = $course_info[0];
			if ($course_info['isshare'] === null) {//未购买课程处理逻辑
				$course_info['pay_status'] = '未支付';
				unset($course_info['isshare']);
				unset($course_info['sound_url']);
				unset($course_info['sound_length']);
				unset($course_info['postpay_poster_url']);
				unset($course_info['pay_method']);
			} else {
				$course_info['pay_status'] = '已支付';
				$course_info['isshare'] === 1 
					? $course_info['isshare'] = '已分享'
					: $course_info['isshare'] = '未分享';
				$course_info['pay_method'] === 1 
					? $course_info['pay_method'] = '分享购买'
					: $course_info['pay_method'] = '直接购买';
			}
			if ($course_info['start_time'] < time()) { 
				$course_info['start'] = true;
			} else {
				$course_info['start'] = false;
			}
			if ($course_info['type'] === 2) { //精品课
				$course_info['start'] = true;
			} else {
				$course_info['start_time'] = date('Y-m-d H:i:s', $course_info['start_time']);
			}
			$course_info['type'] = $course_info['type'] === 1 ? '专栏' : '精品';
			$chief_model = new LctChiefModel();
			$course_info['fans'] = $chief_model->getChiefUserNum($chief_id,$periods);
			//获取已购用户列表
			$user_list = $chief_model->getChiefUser($chief_id,$periods);
			//获取评论轮播
			$comment_list = $this->_model->getCourseComment($course_id);
			//获取讲师答疑列表
			$question_list = $this->_model->getCourseQuestion($course_id);
			//获取相关课程
			$related_list = $chief_model->getChiefRecommend($chief_id, $this->_userinfo['user_id']);
			if (!empty($related_list)) {
				foreach ($related_list as $key => $value) {
					if ($value['pay_method'] === null) {
						$related_list[$key]['pay_status'] = '未支付';//未付款
					} else {
						$related_list[$key]['pay_status'] = '已支付';
					}   
					unset($related_list[$key]['pay_method']);
					$related_list[$key]['fans'] = $chief_model->getChiefUserNum($chief_id,$periods);
				}   
			}
			$ret = [
				'course_info' => $course_info,
				'user_list' => $user_list,
				'comment_list' => $comment_list,
				'question_list' => $question_list,
				'related_list' => $related_list,
				'question_count' => $this->_model->getCourseQuestionCount($course_id)['count']
			];
			return Common_Util::returnJson('20001','查询成功',$ret);
		}
		return Common_Util::returnJson('20002','暂无信息');	
	}

	/**
	 * 获取用户已经学习列表
	 */
	public function studiedAction() {

		$page = Common_Util::getHttpReqQuery($this,'page','Int','n');//第几页
        $size = Common_Util::getHttpReqQuery($this,'size','Int','n');//每页数量

        if (!($page && $size)) {
            $page = 1;
            $size = 3; 
        }

		$studied_list = $this->_model->getstudied($this->_userinfo['user_id'], $page, $size);
		$count = $this->_model->getStudiedCount($this->_userinfo['user_id'])[0]['count'];
		if (!empty($studied_list)) {
			foreach ($studied_list as $key => $value) {
				$studied_list[$key]['sound_length'] = ceil($value['sound_length'] / 60)."分钟";
				$studied_list[$key]['last_timeline'] = ceil($value['last_timeline'] / 60)."分钟";
				if ($value['sound_length'] != 0) {
					$progress = round($value['last_timeline'] / $value['sound_length'], 4) * 100;
					$studied_list[$key]['progress'] = $progress > 100 ? 100 :$progress;
				} else {
					$studied_list[$key]['progress'] = 0;
				}
			}
		}
		$chief_model = new LctChiefModel();
		$recommend_list = $chief_model->getPurchasedRecommend($this->_userinfo['user_id']);
		$chief_model = new LctChiefModel();
		if (!empty($recommend_list)) {
			foreach ($recommend_list as $key => $value) {
				$recommend_list[$key]['fans'] = $chief_model->getChiefUserNum($value['chief_id'],$value['periods']);
				$recommend_list[$key]['pay_method'] = '未支付';
				
			}
		}	
		$ret = [
			'curPage' => $page,
			'curSize' => $size,
			'count' => $count,
			'studied_list' => $studied_list,
			'recommend_list' => $recommend_list
		];

		return Common_Util::returnJson('20001', '查询成功', $ret);

	}

	/**
	 * 设置课程已学习时间点
	 */
	public function addReadTimelineAction() {

		if ($this->getRequest()->getMethod() == "POST") {
			$course_id = Common_Util::getHttpReqQuery($this,'course_id','Int','n');//课程id
			$timeline = Common_Util::getHttpReqQuery($this,'timeline','Str','n');//时间节点
			$periods = Common_Util::getHttpReqQuery($this,'periods','Int','n'); //期数
			$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n'); //主课程ID
			$ret = $this->_model->addReadTimeline($course_id,$this->_userinfo['user_id'], $timeline,$periods,$chief_id);
			if ($ret) {
				return Common_Util::returnJson('20001','修改状态成功');
			} else {
				return Common_Util::returnJson('20006','修改状态失败');
			}
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

}//endclass
