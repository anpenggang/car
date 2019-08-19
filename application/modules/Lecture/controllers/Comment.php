<?php

/**
 *
 * @name CommentController
 * @desc 评论控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Comment.php v0.0 2018/4/9 新建
 */
class CommentController extends BaseController {

	private $_model = null;
	private $_userinfo = null;

	/**
	 * 初始化方法
	 */
	public function init() {
		
		//调用父类的初始化方法
		parent::init();
		
		//实例化课程模型
		$this->_model = new LctCommentModel();

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
	
	}

	/**
	 * 添加评论
	 */
	public function addAction() {

		if ($this->getRequest()->getMethod() === "POST") {
			$data['course_id'] = Common_Util::getHttpReqQuery($this,'course_id','Int','n');//课程id
			$data['content'] = Common_util::getHttpReqQuery($this,'content','Str','n');//评论内容
			$data['user_id'] = $this->_userinfo['user_id'];
			$data['create_time'] = time();
			$ret = $this->_model->addComment($data);
			if ($ret) {
				return Common_Util::returnJson('20001','添加成功');
			}
			return Common_Util::returnJson('20004','添加失败，请重试');
		} else {
			return Common_Util::returnJson('10007','请求方法有误');
		}

	}

	/**
	 * 查询评论
	 */
	public function listAction() {

		$course_id = Common_Util::getHttpReqQuery($this,'course_id','Int','n');//课程id
		$comment_list = $this->_model->getCommentList($course_id);
		if (!empty($commont_list)) {
			return Common_Util::returnJson('20001','查询成功',$comment_list);
		}
		return Common_Util::returnJson('20002','暂无数据');
	}

}//endclass	
