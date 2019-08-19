<?php

/**
 *
 * @name CdpDanmakuModel
 * @desc 弹幕内容模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CdpDanmaku.php v0.0 2017/12/18 新建
 */
class CdpDanmakuModel extends BaseModel {

	private $_table = 'countdownparty_danmaku'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {
		
		//调用父类构造方法
		parent::__construct();		

	}

	/**
	 * 添加弹幕
	 *
	 * @param	Array	$data	弹幕内容
	 * @return  Mixed	$ret	如果成功返回新增弹幕ID;失败返回false
	 */
	public function addDanmaku($data) {
		
		$ret = $this->db->insert($this->_table,$data);
		return $ret;

	}

	/**
	 * 审核弹幕
	 *
	 * @param	Integer		$danmu_id	弹幕id
	 * @return	Boolean		$ret		成功返回true，失败返回false
	 */
	public function reviewDanmaku($danmu_id,$status) {
		
		$this->db->where('id',$danmu_id);
		$ret = $this->db->update($this->_table,['status' => $status]);
		if ($ret) {//审核成功之后，拼接JSON字符串返给小程序
			$sql = "SELECT d.id,d.content,u.nickname,u.dnickname,u.school FROM {$this->_table} d";
			$sql .= " LEFT JOIN countdownparty_minipro_user u ON d.user_id = u.id";
			$sql .= " WHERE d.id = '{$danmu_id}' LIMIT 1";
			$broadcast = $this->db->rawQuery($sql);
			if($broadcast) {
				return json_encode($broadcast[0],JSON_UNESCAPED_UNICODE);
			}
		}	
		return $ret;

	}

	/**
	 * 弹幕审核列表
	 */
	public function reviewingDanmukuList($user_id) {
		$this->db->autocommit(false);//开启事务
		$sql = "SELECT d.id,d.content,d.user_id,u.nickname,u.avatarurl,u.dnickname,u.school FROM {$this->_table} d";
		$sql .= " LEFT JOIN countdownparty_minipro_user u ON d.user_id = u.id";
		$sql .= "  WHERE d.status = 0 AND reviewer  = '{$user_id}' ORDER BY d.id DESC";
		$reminder = $this->db->rawQuery($sql);//分配给该用户上次未审核完的弹幕
		if(count($reminder) < 10) {
			$limit = 10 - count($reminder);
			$reminder_ids = implode(',',array_column($reminder,'id'));
			$reminder_ids = empty($reminder_ids) ? 0 : $reminder_ids;
			$sql = "SELECT d.id,d.content,d.user_id,u.nickname,u.avatarurl,u.dnickname,u.school FROM {$this->_table} d";
			$sql .= " LEFT JOIN countdownparty_minipro_user u ON d.user_id = u.id";
			$sql .= "  WHERE d.status = 0 AND d.reviewer = 0 AND d.id NOT IN ($reminder_ids) ORDER BY d.id DESC limit {$limit}";
			$new = $this->db->rawQuery($sql);//新分配弹幕
			if(!empty($new)){//弹幕表中有多余记录，则更新弹幕审核者id，将弹幕分配给该用户
				$ids = implode(',',array_column($new, 'id'));
				$sql = "UPDATE {$this->_table} SET reviewer = {$user_id} WHERE id IN ($ids)";
				$group = $this->db->rawQuery($sql);	
				if ($group === false) {//更新成功则提交，否则回滚
					$this->db->rollback();
					return $reminder;
				} else {
					$this->db->commit();
					$ret = array_merge($reminder,$new);
					return $ret;
				}
			} else {//如果弹幕表中无多余弹幕则直接返回原数据
				return $reminder;
			}
		} else {//如果用户名下已有十条弹幕则直接返回
			return $reminder;
		}

	}

}

