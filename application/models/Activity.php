<?php

/**
 *
 * @name ActivityModel
 * @desc 活动信息模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Activity.php v0.0 2017/10/9 新建
 */
class ActivityModel extends BaseModel {

	private $_table = 'barrage_activity'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * PC端登录方法
	 * @param	Integer	$roomnum	房间编号
	 * @param	String	$secret_key	密钥
	 * @return	String	$ret		成功返回活动信息，失败返回空数组
	 */
	public function loginDanmaku($roomnum,$secret_key) {

		$sql = "SELECT a.*,u.nickname FROM {$this->_table} a";
		$sql .= " LEFT JOIN barrage_users u ON u.id = a.user_id";
		$sql .= " WHERE a.roomnum = '{$roomnum}' AND a.secret_key = '{$secret_key}'";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取活动信息
	 * @actid	Integer	$actid	活动id
	 * @return	Array	$ret	活动内容
	 */
	public function getActivityById($actid) {

		$sql = "SELECT * FROM $this->_table WHERE id = '{$actid}'";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取上一次弹幕房间编号
	 * 房间编号为一个独立的只有一个自增id字段的表，
	 * 每次新增活动是其id自增加1，作为下一次创建活动的房间编号
	 * @return	Array	$ret	新增活动的房间编号
	 */
	public function getRoomnum() {

		$sql = "SELECT * FROM barrage_roomnum ORDER BY id DESC LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 房间编号加1操作，作为下次新增活动的房间编号
	 * @return	Mixed	$ret	成功返回新增房间编号，失败返回false
	 */
	public function addRoomnum() {

		$ret = $this->db->insert('barrage_roomnum',['id' => null]);
		return $ret;

	}

	/**
	 * 获取用户创建活动的数目
	 * @parram	Integer	$user_id					用户id
	 * @return	Integer	$ret[0]['create_act_num']	id对应用户所创建的活动数目
	 */
	public function getCreateActNum($user_id) {
	
		$sql = "SELECT COUNT(*) as create_act_num FROM $this->_table WHERE user_id='{$user_id}'";
		$ret = $this->db->rawQuery($sql);
		return $ret[0]['create_act_num'];

	}	

	/**
	 * 添加活动
	 * @param	Array	$data	新建活动的信息
	 * @return	Mixed	$ret	如果成功返回新增活动ID;失败返回false
	 */
	public function addAct($data) {

		//开启事务
		$this->db->autocommit(false);
		$act_ret = $this->db->insert($this->_table,$data);
		//创建活动后，维护房间编号表，使其ID自增，作为下一个房间编号
		$roomnum_ret = $this->addRoomnum();
		//提交事务 如果活动添加成功，切房间编号维护成功，提交事务
		if (is_numeric($act_ret) && (0 < $act_ret) && is_numeric($roomnum_ret) && (0 < $roomnum_ret)) {
			$this->db->commit();
		}
		//回滚
		$this->db->rollback();
		return $act_ret;

	}
	/**
	 * 修改活动信息
	 * @param	Array	$data	修改活动的信息
	 * @return	Boolean	$ret	如果修改成功返回true;失败返回false
	 */
	public function updateAct($data) {

		$this->db->where('id',$data['act_id']);
		unset($data['act_id']);
		$ret = $this->db->update($this->_table,$data);
		return $ret;

	}

	/**
	 * 添加活动敏感词
	 * 1. 去敏感词表里查找，如果有记录:
	 *	 1.1.去敏感词活动关联表里面查找，如果有记录且未删除返回不能重复添加同一敏感词
	 *	 1.2.去敏感词活动关联表里面查找，如果有记录但状态为删除，修改状态为正常，返回添加成功
	 *	 1.3.关联表中没有记录，向关联表中写入关联记录即可
	 * 2. 敏感词表中没有敏感词记录
	 *	 2.1.添加敏感词
	 *	 2.2.添加关联记录
	 * @param	Array	$data	敏感词信息
	 * @return	Mixed	$ret	如果成功返回敏感词列表，失败返回false
	 */
	public function addFilterWord($data) {
		
		$this->db->where('content',$data['filter_word']);
		$filter_word = $this->db->getOne('barrage_filterwords');
		// 1
		if (!empty($filter_word)) {
			$this->db->where('act_id',$data['act_id']);
			$this->db->where('filter_word_id',$filter_word['id']);
			$ret = $this->db->getOne('barrage_act_filter');
			if (!empty($ret) && $ret['status'] == 0) { //1.1
				return Common_Util::returnJson('20005','不能重复添加敏感词');
				exit;
			} else if (!empty($ret) && $ret['status'] == 9) {//1.2
				$this->db->where('act_id',$data['act_id']);
				$this->db->where('filter_word_id',$filter_word['id']);
				$ret = $this->db->update('barrage_act_filter',['status'=>0]);
				return Common_Util::returnJson('20004','敏感词添加成功');
			}
			// 1.3
			$param = [
				'act_id' => $data['act_id'],
				'filter_word_id' => $filter_word['id'],
				'create_time' => time()
			];
			$this->db->insert('barrage_act_filter',$param);
			return Common_Util::returnJson('20004','敏感词添加成功');
			exit;
		}
		// 2.1
		$param = [
			'content' => $data['filter_word'],
			'create_time' => time()
		];
		$filter_word_id = $this->db->insert('barrage_filterwords',$param);
		// 2.2
		if ($filter_word_id > 0) {
			$param = [
				'act_id' => $data['act_id'],
				'filter_word_id' => $filter_word_id,
				'create_time' => time()
			];
			$this->db->insert('barrage_act_filter',$param);
			return Common_Util::returnJson('20004','敏感词添加成功');
			exit;
			
		}
		return Common_Util::returnJson('10004','操作失败，请重试');
		exit();

	}

	/**
	 * 获取活动对应的敏感词
	 * @param	Integer	$act_id	活动ID
	 */
	public function getFilterWords($act_id) {

		//1从关联表中获取活动对应敏感词id 
		$this->db->where('act_id',$act_id);
		$this->db->where('status',0);
		$filter_word_ids = $this->db->get('barrage_act_filter',null,['filter_word_id']);
		if (empty($filter_word_ids)) {
			return false;
			exit;
		}

		//2根据敏感词ID从敏感词表中获取敏感词
		//将$filter_word_ids拼装成字符串再查询，避免反复请求数据库
		$ids = '';
		foreach ($filter_word_ids as $filter_word_id) {
			$ids .= $filter_word_id['filter_word_id'].',';
		} 
		$ids = chop($ids,',');
		$sql = "SELECT id,content FROM barrage_filterwords WHERE id in ({$ids})";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 删除活动对应的敏感词
	 * @param	Integer	$act_id 活动ID
	 * @param	Integer	$filter_word_id	敏感词ID
	 * @return	Boolean $ret	成功返回true 失败返回false
	 */
	public function delFilterWord($act_id,$filter_word_id) {

		$this->db->where('act_id',$act_id);
		$this->db->where('filter_word_id',$filter_word_id);
		$ret = $this->db->update('barrage_act_filter',['status'=>9]);
		return $ret;

	}
	

	/**
	 * 修改活动状态
	 * @param	Integer	$act_id	要修改活动ID
	 * @return	Boolean $ret	成功返回true;失败返回false
	 */
	public function updateActStatus($act_id,$status) {
	
		$this->db->where('id',$act_id);
		$ret = $this->db->update($this->_table,['status' => $status]);
		return $ret;
	}
	
	/**
	 * 生成活动二维码
	 * @param	Array	$data	活动二维码信息
	 * @return	Boolean	$ret	成功返回true;失败返回false
	 */
	public function updateQrcode($act_id,$qrcode_path) {

		//储存二维码
		$this->db->where('id',$act_id);
		$ret = $this->db->update($this->_table,['qr_code' => $qrcode_path]);
		return $ret;

	}

	/**
	 * 查询我创建的活动
	 * @param	Integer	$user_id	查询条件
	 * @return	Boolean $ret	成功时返回我创建活动信息；失败时返回false
	 */
	public function getMyCreateActivity($user_id) {

		$sql = "SELECT * FROM {$this->_table} WHERE user_id = '{$user_id}' ORDER BY id DESC LIMIT 3";//线上只返回三条
		//$sql = "SELECT * FROM {$this->_table} WHERE user_id = '{$user_id}' ORDER BY id DESC";//测试用
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 修改活动审核状态
	 * @param	Integer	$act_id 	 活动ID
	 * @param	Integer	$bar_check	 状态码 0 开启审核（默认） 1 关闭审核
	 */
	public function updateActivityCheck($act_id,$bar_check) {
	
		$this->db->where('id',$act_id);
		$ret = $this->db->update($this->_table,['bar_check'=>$bar_check]);
		return $ret;		

	}
	
	/**
	 * 活动过滤器开关
	 * @param	Integer	$act_id	活动ID
	 * @param	Integer	$filter_status	开关状态 0关闭 1开启
	 * @return	Boolean	$ret	成功返回true 失败返回false
	 */
	public function filterStatus($act_id,$filter_status) {
		
		$this->db->where('id',$act_id);
		$ret = $this->db->update($this->_table,['filter_status'=>$filter_status]);
		return $ret;
	}

	/**
	 * 根据活动id获取活动所对应的房间编号,用户判断用户屏幕是否在线
	 * @param	Integer	$act_id	活动ID
	 * @return	Array	$ret	房间编号
	 */
	public function	getRoomnumById($act_id) {

		$this->db->where('id',$act_id);
		$ret = $this->db->getOne($this->_table,'roomnum');
		return $ret;

	}
	/**
	 * 获取活动管理员user_id 和活动是否状态,
	 * 1.用户当管理员发送弹幕的时候直接修改状态为审核通过
	 * 2.当活动状态不是正常时，不允许其发送弹幕
	 * 3.roomnum 当前活动的房间号
	 * @param	Integer	$act_id	活动ID
	 * @return	Array	$ret	返回活动的管理人员id和活动状态
	 */
	public function getActManagerById($act_id) {

		$this->db->where('id',$act_id);
		$ret = $this->db->getOne($this->_table,['user_id','status','roomnum','bar_check','show_nickname']);
		return $ret;

	}

	/**
	 * 根据房间号，获取活动是否存在，清空抽奖屏幕时使用
	 */
	public function getActByRoomnum($roomnum) {
	
		$this->db->where('roomnum',$roomnum);
		$ret = $this->db->getOne($this->_table,['id']);
		return $ret;
	
	}

}
