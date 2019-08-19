<?php 

/**
 *
 * @name UserModel
 * @desc 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version User.php v0.0 2017/9/30 新建
 */
class IndexModel extends BaseModel {

	private $_table = 'countdownparty_minipro_user'; 

	/**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() { 
		
		//调用父类构造方法
		parent::__construct();

	}
	
	public function updateUserInfo() {
		
		$this->db->where('id','2');
		$this->db->update($this->_table,['school'=>'','dnickname'=>'']);
		$this->db->where('id','3');
		$this->db->update($this->_table,['school'=>'','dnickname'=>'']);

	}
}
