<?php
class PtnBannerModel extends BaseModel {
		private $_table = 'partner_minipro_banner';
			
		/*
		 *获取banner详情
		 */
		public function getBannerInfo(){
			$time = time();
			$sql="select imgsrc,jump_address,obj_id,start_time,end_time,obj_type from partner_minipro_banner where start_time<$time and end_time>$time and status=1 order by weight asc";
			$ret= $this->db->rawQuery($sql);
			return empty($ret)?false:$ret;
		}
}	
?>
