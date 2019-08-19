<?php

/**
 *
 * @name QuestionController
 * @desc FAQ控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Question.php v0.0 2018/4/9 新建
 *
 */
class QuestionController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {
		
		//调用父类的初始化方法
		parent::init();
		
		//实例化课程模型
		$this->_model = new LctQuestionModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
	
	}

	/**
	 * 添加提问
	 */
	public function addAction() {

		if ($this->getRequest()->getMethod() === 'POST') {
			$data['course_id'] = Common_Util::getHttpReqQuery($this,'course_id','Int','n'); 
			$data['question'] = Common_Util::getHttpReqQuery($this,'question','Str','n');		
			$data['user_id'] = $this->_userinfo['user_id'];
			$data['create_time'] = time();
			$ret = $this->_model->addQuestion($data);
			if ($ret) {
				return Common_Util::returnJson('20001','添加成功');
			}
			return Common_Util::returnJson('20004','添加失败,请重试');
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 问题列表
	 */
	public function listAction() {

		$course_id = Common_Util::getHttpReqQuery($this,'course_id','Int','n');
		$page = Common_Util::getHttpReqQuery($this,'page','Int','n');
		$size = Common_Util::getHttpReqQuery($this,'size','Int','n');
		$ret = $this->_model->questionList($course_id, $page, $size);
		if (!empty($ret)) {
			$count = $this->_model->questionCount($course_id)[0]['count'];
			$data = [ 
				'item' => $ret,
				'curPage' => $page,
				'curSize' => $size,
				'count' => $count
			]; 
			return Common_Util::returnJson('20001','查询成功',$data);
		}
		return Common_Util::returnJson('20002','暂无数据');
	}

}//endclass
