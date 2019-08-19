<?php

class Film_List extends BaseModel{

	private $_table = 'orders';
	public $_pagesize = 10 ; 
	public function __construct(){

		parent::__construct();
	}


	public function ListGet($page){

		$offset = ($page - 1)*$this->_pagesize ;
		$sql = "select * from $this->_table  ";
		$sql.= "order by id desc limit $offset ,$this->_pagesize";
		$ret = $this->db->rawQuery($sql);
		return $ret;
	}


	public function IsLast($count){

		if($count >= $this->_pagesize){
			return 'N';
		}
		return 'Y';
	}

	public function HasOrders($userid,$documentid){

		$sql = "select id from $this->_table where userid='$userid' and documentid=$documentid and status=1 limit 1";
		$ret = $this->db->rawQuery($sql);
		return empty($ret)?false:true;
	}

}	

?>
