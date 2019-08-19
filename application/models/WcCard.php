<?php
class WcCardModel extends BaseModel{
	
	private $_table = 'mood_card';

	//构造方法，调用基类，获取连接数据库的实例
	public function __construct(){
		
		//获取数据库实例
		parent::__construct();
	}
	
	/**
	 *获取所有心愿卡
	 */
	public function getAllWishcard($userid){
		
		$data = $this->db->get($this->_table,null,'image,id');
		if(!empty($data)){
		
			foreach($data as $k=>$v){
				
				$sql = "select * from mood_collect where userid=$userid and card_id={$v['id']}";		
				$ret = $this->db->rawQuery($sql);
				if($ret){
					$data[$k]['collert'] = 'yes';
				}else{
					$data[$k]['collert'] = 'no';
				}
				
			}
		}
		return $data;
	} 
	
	/**
	 *获取我的心愿卡
	 */
	public function MyCardList($userid){
		
		$this->db->where('userid',$userid);
		$data = $this->db->get($this->_table,null,'id,image');
		return $data;

	}

	/**
	 *获取我收藏的卡片
	 */
	public function myCollect($userid){
		
		$sql="select a.image,a.id as card_id from $this->_table a inner join mood_collect b on a.id=b.card_id where b.userid=$userid";
		$ret = $this->db->rawQuery($sql);	
		return $ret;
	}

	/**
	 *收藏或取消收藏
	 */
	public function collect($userid,$card_id){
		
		$this->db->where('userid',$userid);
		$this->db->where('card_id',$card_id);
		$data = $this->db->get('mood_collect');

		if(!empty($data)){

			$this->db->where('userid',$userid);
			$this->db->where('card_id',$card_id);
			$ret = $this->db->delete('mood_collect');
			return $ret;
		
		}else{

			$data=[];
			$data['userid'] = $userid;
			$data['card_id'] = $card_id;
			$ret = $this->db->insert('mood_collect',$data);
			return $ret;
			
		}
		
	}

	/**
  	 *添加心愿卡
	 */
	public function addCard($data){
	
		$ret = $this->db->insert($this->_table,$data); 	
		return $ret;
		
	}
	
		
}
?>
