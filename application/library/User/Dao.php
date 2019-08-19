<?php

class User_Dao extends BaseModel{

	private $_table = 'userinfo'; 
	public function __construct(){

		parent::__construct();
	}

	public function GetUserCity($userid){
		$this->db->where("userid",$userid);
		$ret = $this->db->getOne($this->_table,'cityid');
		return $ret;
	}

	public function GetUserInfo($userid,$column = '*'){
		$this->db->where("userid",$userid);
		$ret = $this->db->getOne($this->_table,$column);
		return $ret;
	}


//class
}

?>
