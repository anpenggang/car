<?php

/**
 *
 * @name LctChief.php
 * @desc Lecture_Chief 主课程模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version LctChief.php v1.0 2018/5/11 新建
 */
class LctChiefModel extends BaseModel {

	private $_table = 'lecture_chief';
	private $_chief_course_table = 'lecture_chief_course';

	/**
	 * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 获取主课程列表
	 */
	public function getChiefList($user_id,$page,$size) {
	
		$limit_start = ($page-1) * $size;
		$sql = "SELECT cc.chief_id,MAX(cc.periods) as periods,cc.periods_start_time,cc.cover_url,c.title,c.type,c.speaker,uc.pay_method,uc.isshare";
		$sql .= " FROM {$this->_chief_course_table} cc";
		$sql .= " LEFT JOIN {$this->_table} c ON cc.chief_id=c.id";
		$sql .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id=cc.chief_id AND uc.user_id={$user_id} AND uc.periods=cc.periods";
		$sql .= " WHERE c.status = 1"; 
		$sql .= " GROUP BY cc.chief_id";
		$sql .= " ORDER BY c.weight DESC, cc.id DESC";
		$sql .= " LIMIT {$limit_start},$size";
		return $this->db->rawQuery($sql);
	
	}
	
	/**
	 * 获取主课程的数目
	 */
	public function getChiefCount($user_id) {

		$sql = "SELECT COUNT(DISTINCT(cc.chief_id)) as count FROM {$this->_chief_course_table} cc";
		$sql .= " LEFT JOIN {$this->_table} c ON cc.chief_id=c.id";
		$sql .= " WHERE c.status = 1"; 
		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取主课程详情
	 */
	public function getChiefDetail($chief_id, $user_id, $periods) {

		$sql = "SELECT cc.periods_start_time,cc.chief_id,MAX(cc.periods) as periods,cc.periods_start_time,cc.cover_url,c.title,c.type,c.speaker,uc.pay_method,c.prepay_poster_url,c.postpay_poster_url,c.price_prime,c.share_discount";
		$sql .= " FROM {$this->_chief_course_table} cc";
		$sql .= " LEFT JOIN {$this->_table} c ON c.id={$chief_id}";
		$sql .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id={$chief_id} AND uc.periods={$periods} AND uc.user_id={$user_id}";
		$sql .= " WHERE cc.chief_id={$chief_id} AND cc.periods={$periods}"; 
		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取已学习用户列表 限制十个
	 */
	public function getChiefUser($chief_id,$periods) {

		$sql = "SELECT u.nickname,u.avatar_url";
		$sql .= " FROM lecture_user_chief uc";
		$sql .= " LEFT JOIN lecture_user u ON u.id = uc.user_id";
		$sql .= " WHERE uc.chief_id = {$chief_id} AND uc.periods = {$periods}";
		$sql .= " ORDER BY uc.id DESC";
		$sql .= " LIMIT 6";

		return $this->db->rawQuery($sql);

	}

	/**
	 * 获取某个课程已学习用户的数目
	 */
	public function getChiefUserNum($chief_id,$periods) {

		$sql = "SELECT (base_fans + (SELECT COUNT(1) FROM lecture_user_chief uc where uc.chief_id = {$chief_id} AND uc.periods = {$periods})) as fans";
		$sql .= " FROM $this->_table";
		$sql .= " WHERE id = {$chief_id}";
		$ret = $this->db->rawQuery($sql);
		
        $handler_num = $ret[0]['fans'];

        if ($handler_num > 10000 ) { 
            $handler_num = substr($handler_num, 0, -4).'.'.substr($handler_num, -4, -3)."万+";
        }
        return $handler_num;	
	}

	/**
	 * 获取用户已购买课程列表
	 */
	public function getpurchased($user_id,$page,$size) {
	
		if ($page == 1) {
			$limit_start = 0;
			$size = 3;
		} else if ($page == 2) {
			$limit_start = 3;
		} else if ($page >= 3) {
			$limit_start = ($page-2) * $size + 3;
		}
		$sql = "SELECT uc.chief_id,c.title,c.type,c.speaker,uc.periods,uc.pay_method,cc.cover_url,cc.periods_start_time"; 
		$sql .= " FROM lecture_user_chief uc";
		$sql .= " LEFT JOIN lecture_chief c ON c.id = uc.chief_id";
		$sql .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id=c.id AND cc.periods=uc.periods";
		$sql .= " WHERE uc.user_id = {$user_id}";
		$sql .= " GROUP BY uc.chief_id,uc.periods";
		$sql .= " ORDER BY uc.id DESC";
		$sql .= " LIMIT {$limit_start},$size";
		return  $this->db->rawQuery($sql);

	}

	/**
	 * 获取用户已购买课程的数量
	 */
	public function getpurchasedCount($user_id) {

		$sql = "SELECT COUNT(1) as count"; 
		$sql .= " FROM lecture_user_chief";
		$sql .= " WHERE user_id = {$user_id}";
		return  $this->db->rawQuery($sql);

	}

	/**
	 * 根据主课程ID和期数获取开课时间和主课程封面url
	 */
	public function getChiefInfo($chief_id,$periods) {

		$this->db->where('chief_id', $chief_id)->where('periods',$periods);
		return $this->db->getOne($this->_chief_course_table,['periods_start_time','cover_url']);

	}

	/**
	 * 根据主课程ID获取课程详情
	 */
	public function getChiefInfoById($chief_id) {

		$this->db->where('id', $chief_id);
		return $this->db->getOne($this->_table);

	}

	/**
	 * 在已购清单下推荐课程
	 */
	public function getPurchasedRecommend($user_id) {

		$sql = "SELECT c.id as chief_id,c.title,c.speaker,c.res_cover_url,MAX(cc.periods) as periods";
		$sql .= " FROM {$this->_table} c";
		$sql .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id = c.id";
		$sql .= " WHERE c.id NOT IN (SELECT chief_id FROM lecture_user_chief WHERE user_id = {$user_id}) AND c.status = 1 AND cc.periods IS NOT NULL";
		$sql .= " GROUP BY cc.chief_id";
		$sql .= " ORDER BY c.id DESC";
		$sql .= " LIMIT 2";
		return $this->db->rawQuery($sql);

	}

	/**
	 * 更新用户已经购买课程状态为已经分享
	 */
	public function updateUserChief($chief_id,$user_id,$data) {

		$this->db->where('chief_id',$chief_id)->where('user_id',$user_id);
		return $this->db->update('lecture_user_chief',$data);

	}
	
	/**
	 * 获取课程课程剩余购买人数
	 */
	public function getLimitNum($chief_id,$periods) {

		$this->db->where('chief_id',$chief_id)->where('periods',$periods);
		$limit_num = $this->db->getOne($this->_chief_course_table,['limit_num']);
		$sql = "SELECT (base_fans + (SELECT COUNT(1) FROM lecture_user_chief uc where uc.chief_id = {$chief_id} AND uc.periods = {$periods})) as fans";
		$sql .= " FROM $this->_table";
		$sql .= " WHERE id = {$chief_id}";
		$ret = $this->db->rawQuery($sql);
		
		$num = $limit_num['limit_num'] - $ret[0]['fans'];
		return $num > 0 ? $num : 0;

	}

	/**
	 * 根据某课程标签推荐课程
	 */
	public function getChiefRecommend($chief_id, $user_id) {

		//采用标签重叠度来计算课程
		$sql = "SELECT tag_id FROM lecture_chief_tag WHERE chief_id={$chief_id}";
		$tag_result = $this->db->rawQuery($sql);
		if (empty($tag_result)) {//课程没有标签，则从课程表里面取出最新的两条返给用户
			$sql2 = "SELECT c.id,c.title,c.speaker,c.res_cover_url,uc.pay_method,MAX(cc.periods) as periods FROM {$this->_table} c";
			$sql2 .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id = c.id AND uc.user_id = {$user_id}";
			$sql2 .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id = c.id";
			$sql2 .= " WHERE c.id != {$chief_id} AND c.status = 1 AND cc.periods IS NOT NULL"; 
			$sql2 .= " GROUP BY cc.chief_id";
			$sql2 .= " ORDER BY c.id DESC";
			$sql2 .= " LIMIT 2";
			$course_result = $this->db->rawQuery($sql2);
			return $course_result;
		
		}
	
		//查询出标签权重最高的记录（限制最多两条）权重标准:与原课程标签重叠度越高,则权重越高
		$tag_ids = implode(',', array_column($tag_result,'tag_id'));	
		$sql3 = "SELECT count(1) as count,chief_id FROM lecture_chief_tag";
		$sql3 .= " WHERE tag_id IN ({$tag_ids}) AND chief_id != {$chief_id}";
		$sql3 .= " GROUP BY chief_id ";
		$sql3 .= " ORDER BY count DESC";
		$sql3 .= " LIMIT 2";
		$recommend_result = $this->db->rawQuery($sql3);
		$chief_ids = implode(',',array_column($recommend_result,'chief_id'));
		if (empty($chief_ids)) {
			$chief_ids = 0;
		}
		
		//根据权重高的标签查询出课程
		$sql4 = "SELECT c.id,c.title,c.speaker,uc.pay_method,c.res_cover_url,MAX(cc.periods) as periods FROM {$this->_table} c";
		$sql4 .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id = c.id AND uc.user_id = {$user_id}";
		$sql4 .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id = c.id";
		$sql4 .= " WHERE c.id in ({$chief_ids}) AND c.id != {$chief_id} AND c.status = 1 AND cc.periods IS NOT NULL";
		$sql4 .= " GROUP BY cc.chief_id";
		$sql4 .= " ORDER BY c.id DESC";
		$sql4 .= " LIMIT 2";
		
		$course_result = $this->db->rawQuery($sql4);

		//如果推荐课程小于2.则再从课程表中取出最新的课程进行推荐
		if (count($course_result) < 2) {

			$limit = 2 - count($course_result);
			$sql5 = "SELECT c.id,c.title,c.speaker,uc.pay_method,c.res_cover_url,MAX(cc.periods) as periods FROM {$this->_table} c";
			$sql5 .= " LEFT JOIN lecture_user_chief uc ON uc.chief_id = c.id AND uc.user_id = {$user_id}";
			$sql5 .= " LEFT JOIN lecture_chief_course cc ON cc.chief_id = c.id";
			$sql5 .= " WHERE c.id != {$chief_id} AND c.status = 1 AND cc.periods IS NOT NULL";
			$sql5 .= " GROUP BY cc.chief_id";
			$sql5 .= " ORDER BY c.id DESC";
			$sql5 .= " LIMIT {$limit}";
			$course_result2 = $this->db->rawQuery($sql5);	
			$course_result = array_merge($course_result,$course_result2);

		}

		return $course_result;			
	}

	/**
	 * 获取某期课程的时间跨度
	 */
	public function getSpanPeriods($chief_id,$periods) {

		$sql = "SELECT periods_start_time,MAX(start_time) AS periods_end_time";
		$sql .= " FROM {$this->_chief_course_table}";
		$sql .= " WHERE chief_id={$chief_id} AND periods={$periods}";
		$ret = $this->db->rawQuery($sql);
		$start_time = date('Y-m-d', $ret[0]['periods_start_time']);
		$end_time = date('Y-m-d', $ret[0]['periods_end_time']);
		if ($start_time == $end_time) {
			return $start_time;
		}
		return $start_time.'-'.$end_time; 	

	}

}//endclass
