<?php

/**
 *
 * @name RaffleModel
 * @desc 抽奖模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Raffle.php v0.0 2017/10/12 新建
 */
class RaffleModel extends BaseModel {

	private $_table = 'barrage_raffle'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类构造方法
		parent::__construct();

	}

	/**
	 * 检测抽奖人数是否小于弹幕墙活动人数
	 *
	 * @param	Array	$data	需要检测的数据
	 * @return	Int		$ret	返回弹幕墙的活动人数
	 */
	public function checkRaffle($data) {
		
		//检测弹幕墙上面发送过弹幕的人数量 且该用户不在该活动屏蔽列表中
		$sql = "SELECT DISTINCT user_id FROM barrage_content WHERE act_id={$data['act_id']} AND check_status=1";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 新增一条抽奖记录
	 *
	 * @param	Array	$data	新增的记录信息
	 * @return	Integer	$ret	返回新增记录的ID
	 */
	public function addRaffle($data) {

		if (empty($data)) {
			return Common_Util::returnJson('10006','数据传输错误');
		}

		$ret = $this->db->insert($this->_table,$data);
		return $ret;

	} 
	
	/**
	 * 查询中奖人员信息表
	 *
	 * @param	Integer	$act_id	活动ID
	 * @return	Array	$ret	中奖人员信息
	 */
	public function showRaffle($act_id) {
		
		//返回查询记录
		$sql = "SELECT u.nickname,u.avatarUrl,r.level_num,(SELECT COUNT(*) FROM barrage_content c WHERE r.user_id=c.user_id and c.act_id='{$act_id}') as bar_num FROM barrage_raffle r LEFT JOIN barrage_users u ON r.user_id = u.id WHERE act_id = '{$act_id}' ORDER BY r.id DESC";
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}
	
	/**
	 * 给PC端显示中奖信息
	 */
	public function showUsersPortraitForPC($act_id) {
	
		//返回查询记录
		$sql = "SELECT DISTINCT c.user_id as user_id,u.nickname,u.avatarUrl ";
		$sql .= "FROM barrage_content c LEFT JOIN barrage_users u ON c.user_id=u.id WHERE c.act_id='{$act_id}' LIMIT 50";
		$ret = $this->db->rawQuery($sql);
		//print_r($ret);
		return $ret;

	}
	
	/**
	 * 维护用户进行第几次抽奖
	 *
	 * @param	Integer	$act_id	活动ID
	 * @return	Array	$ret	返回上次抽奖的level
	 */
	public function raffleNum($act_id) {

		//进行level_num字段维护，用户进行多次抽奖进行递增
		$sql = "SELECT level_num FROM {$this->_table} WHERE act_id = '{$act_id}' ORDER BY id DESC LIMIT 1";
		$ret = $this->db->rawQuery($sql);
		return $ret;
	}

	/**
	 * 暂时在C层调用的是showRaffle控制器,此方法暂时留用，后期可删除
	 */
	public function showRaffleByLevel($act_id) {

		//返回查询记录
		$sql = "SELECT u.nickname,u.avatarUrl,r.*,(SELECT COUNT(*) FROM barrage_content c WHERE r.user_id=c.user_id and c.act_id='{$act_id}') as bar_num FROM barrage_raffle r LEFT JOIN barrage_users u ON r.user_id = u.id WHERE act_id = '{$act_id}'";
		$ret = $this->db->rawQuery($sql);
		return $ret;
	}

}
