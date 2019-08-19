<?php

/**
 *
 * @name LctCourse.php
 * @desc Lecture_course 课程模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctCourse.php v0.0 2018/4/13 新建
 */
class LctCourseModel extends BaseModel {

	private $_table = 'lecture_course';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 获取课程列表
	 */
	public function getCourseList($page,$size,$chief_id,$periods,$user_id) {

		$limit_start = ($page-1) * $size;
		$sql = "SELECT cc.start_time,c.title,c.cover_url,c.id,uc.pay_method,uc.isshare";
		$sql .= " FROM lecture_chief_course cc";
		$sql .= " LEFT JOIN {$this->_table} c ON cc.course_id=c.id";
		$sql .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id={$chief_id} AND uc.periods={$periods} AND uc.user_id={$user_id}";
		$sql .= " WHERE cc.chief_id={$chief_id} AND cc.periods={$periods}";
		$sql .= " ORDER BY cc.start_time ASC";
		$sql .= " LIMIT {$limit_start},$size";
		return $this->db->rawQuery($sql);

	}

	/**
	 *获取课程总数
	 */
	public function getCourseCount($chief_id,$periods) {

		$sql = "SELECT COUNT(1) AS count";
		$sql .= " FROM lecture_chief_course cc";
		$sql .= " LEFT JOIN {$this->_table} c ON cc.course_id=c.id";
		$sql .= " WHERE cc.chief_id={$chief_id} AND cc.periods={$periods}";
		return $this->db->rawQuery($sql);

	}

	/**
	 * 课程详情
	 */
	public function detailCourse($course_id,$user_id,$chief_id,$periods) {

		$sql = "SELECT c.id,c.title,c.cover_url,chief.speaker,chief.type,c.prepay_poster_url,c.postpay_poster_url,c.sound_url,c.sound_length,IFNULL(us.last_timeline,0) AS last_timeline,uc.isshare,cc.start_time,uc.pay_method";
		$sql .= " FROM {$this->_table} c";
		$sql .= " LEFT JOIN lecture_user_studied us ON c.id = us.course_id AND us.user_id = {$user_id}";
		$sql .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id={$chief_id} AND uc.periods={$periods} AND uc.user_id={$user_id}";
		$sql .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id={$chief_id} AND cc.course_id={$course_id} AND cc.periods={$periods}";
		$sql .= " LEFT JOIN lecture_chief chief ON chief.id={$chief_id}";
		$sql .= " WHERE c.id = {$course_id}";
		$sql .= " LIMIT 1";
		return $this->db->rawQuery($sql);
	
	}

	/**
	 * 获取某课程的评论列表
	 */
	public function getCourseComment($course_id) {

		$sql = "SELECT u.avatar_url,c.content FROM lecture_comment c";
		$sql .= " LEFT JOIN lecture_user u ON u.id = c.user_id";
		$sql .= " WHERE c.course_id = {$course_id} AND c.isdel=2 AND c.isplay=1";
		$sql .= " ORDER BY c.id DESC";
		$sql .= " LIMIT 20";
		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取讲师答疑列表
	 */
	public function getCourseQuestion($course_id) {

		$sql = "SELECT u.avatar_url,q.question,q.solution,CASE q.solution WHEN '' THEN '未回答' ELSE '已回答' END AS status";
		$sql .= " FROM lecture_question q";
		$sql .= " LEFT JOIN lecture_user u ON u.id = q.user_id";
		$sql .= " WHERE q.course_id = {$course_id} AND q.isdel=2 AND q.isplay=1";
		$sql .= " ORDER BY q.id DESC";
		$sql .= " LIMIT 2";
		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取提问总数
	 */
	public function getCourseQuestionCount($course_id) {

		$sql = "SELECT COUNT(1) AS count FROM lecture_question WHERE course_id = {$course_id}";
		return $this->db->rawQuery($sql)[0];

	}

	/** 
	 * 获取用户已经学习列表
	 */
	public function getstudied($user_id,$page,$size) {

		if ($page == 1) {
			$limit_start = 0;
			$size = 3;
		} else if ($page == 2) {
			$limit_start = 3;
		} else {
			$limit_start = ($page-2) * $size + 3;
		}   
		$sql = "SELECT us.id,c.title,c.cover_url,c.sound_length,us.last_timeline,us.chief_id,us.course_id,us.periods"; 
		$sql .= " FROM lecture_user_studied us";
		$sql .= " LEFT JOIN lecture_course c ON c.id = us.course_id";
		$sql .= " WHERE us.user_id = {$user_id}";
		$sql .= " ORDER BY us.update_time DESC";
		$sql .= " LIMIT {$limit_start},$size";
		return  $this->db->rawQuery($sql);

	}


	/**
	 * 获取用户已购买课程的数量
	 */
	public function getStudiedCount($user_id) {

		$sql = "SELECT COUNT(1) as count"; 
		$sql .= " FROM lecture_user_studied";
		$sql .= " WHERE user_id={$user_id}";
		return  $this->db->rawQuery($sql);

	}

	/**
	 * 设置已学习课程节点
	 */
	public function addReadTimeline($course_id,$user_id, $timeline, $periods,$chief_id) {

		$this->db->where('course_id', $course_id)->where('user_id',$user_id)->where('periods',$periods);
		if (!empty($this->db->getOne('lecture_user_studied'))) { //更新时间节点
			$this->db->where('course_id', $course_id)->where('user_id',$user_id)->where('periods',$periods)->where('chief_id', $chief_id);
			return $this->db->update('lecture_user_studied',['last_timeline' => $timeline, 'update_time'=>time()]);
		} else { //新增
			return $this->db->insert('lecture_user_studied',[
				'course_id' => $course_id,
				'chief_id' => $chief_id,
				'periods' => $periods,
				'user_id' => $user_id,
				'last_timeline' => $timeline,
				'create_time' => time()
			]);
		}

	}

}//endclass
