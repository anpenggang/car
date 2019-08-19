<?php

/**
 *
 * @name PtnUser.php
 * @desc partner_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version PtnUser.php v0.0 2018/3/5 新建
 * @version PtnUser.php v0.1 2018/3/12 添加方法 getMyInvite
 */
class PtnUserModel extends BaseModel {

	private $_table = 'partner_minipro_user';

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {

		//调用父类的构造方法
		parent::__construct();

	}

	/**
	 * 根据openID,获取用户信息
	 *
	 * @param	String	$openid	openid
	 * @return	Array	$$ret	用户信息
	 */
	public function getUserByOpenid($openid) {

		$this->db->where('openid',$openid);
		$ret = $this->db->getOne($this->_table);
		return $ret;
	}

	/**
	 * 添加用户
	 *
	 * @param	Array	$data	新建用户信息
	 * @return	Mixed	$ret	如果成功返回新增用户ID，失败返回false
	 */
	public function addUser($data) {

		return $this->db->insert($this->_table,$data);

	}

	/**
	 * 更新用户头像和昵称
	 *
	 * @param	String	$openid	openid
	 * @param	Array	$data	用户信息
	 * @return	Mixed	$ret	如果成功返回用户信息，失败返回false
	 */
	public function updateUserInfo($openid,$data) {

		$this->db->where('openid',$openid);
		$ret = $this->db->update($this->_table,$data);
		if($ret) {
			$ret = $this->getUserByOpenid($openid);	
		}
		return $ret;

	}

	/**
	 * 获取用户信息
	 *
	 * @param	Integer	$id		用户id
	 * @return	Array	$ret	用户信息
	 */
	public function getUserInfo($id) {
	
		$this->db->where('id',$id);
		$ret = $this->db->getOne($this->_table);
		return $ret;

	}

	//获取用户昵称和头像
	public function getNickname($id) {
	
		$usertable = $this->_table;
		$sql="select nickname,avatarUrl from $usertable where id=$id";
		$nickname = $this->db->rawQuery($sql);
		return $nickname[0];

	}

 	//获取用户信息非微信提供
	public function getTrueUserinfo($id){
		$usertable = $this->_table;
		$sql="select realname,school_id,enter_school_year,mobilephone,student_ID_url ,verifystatus from $usertable where id=$id";
		$trueuserinfo = $this->db->rawQuery($sql);
		 //if(!empty($trueuserinfo)){ 2018/3/19 晚 19:30 修改
		if(!empty($trueuserinfo[0]['school_id'])){
			$sql="select name from stuhui_school_info2 where id={$trueuserinfo[0]['school_id']}";
			$schoolname = $this->db->rawQuery($sql);
			$trueuserinfo[0]['schoolname'] = $schoolname[0]['name'];
			$trueuserinfo[0]['student_ID_url'] = str_replace('|',',',$trueuserinfo[0]['student_ID_url']);
			$trueuserinfo[0]['sketch_img'] = 'http://img.stuhui.com/ecea748f9cfc6e7ff94ef87d72a17444';
		}
		return $trueuserinfo[0];

	}
	
	//更新用户详情
	public function UpUserinfo($data,$id){
			$this->db->where('id',$id);
			$ret = $this->db->Update($this->_table,$data);
			return $ret;
		}
		
	
	//获取学校名称
	public function getSchoolId($schoolname){
		$sql = "select id from stuhui_school_info2 where name='{$schoolname}'";
		$ret = $this->db->rawQuery($sql);        
		return empty($ret)?false:$ret[0]['id'];
	}

	//获取学校name
    public function getSchoolName($school_id){
        $sql = "select name from  stuhui_school_info2 where id=$school_id";
        $ret = $this->db->rawQuery($sql);
        return empty($ret)?false:$ret[0]['name'];
    }
	
	//获取学校所在的省份
	public function getStuProvince($schoolname){
		$this->db->where('name',$schoolname);
		$ret = $this->db->getOne('stuhui_school_info2','province');
		return empty($ret)?false:$ret;
	}

	/**
	 * 获取我邀请的用户 信息
	 *
	 * @param	Integer		$user_id		用户id
	 * @param	Integer		$page			页码
	 * @param	Integer		$size			每页显示的数量
	 * @return	Array		$ret			被邀请用户列表
	 */
	public function getMyInvite($user_id,$page,$size) {

		$limit_start = ($page-1) * $size;
		$sql = "SELECT nickname,avatarUrl FROM {$this->_table} WHERE referee_id={$user_id}";
		$sql .= " ORDER BY id DESC LIMIT {$limit_start},$size";
		
		$ret = $this->db->rawQuery($sql);
		return $ret;

	}

	/**
	 * 获取我邀请的用户的总数
	 * @param	Integer		$user_id		用户id
	 */
	public function getMyInviteCount($user_id) {

		$sql = "SELECT COUNT(1) AS count FROM {$this->_table} WHERE referee_id={$user_id}";
		return $this->db->rawQuery($sql);

	}
	
	public function checkPhone($phone){
		$sql="select * from partner_minipro_user where mobilephone = '{$phone}' ";
        $ret = $this->db->rawQuery($sql);
		//error_log(print_r($ret,true));
		if(!empty($ret)){
			return false;	
		}else{
			return true;
		}		
	}
	
}//class
