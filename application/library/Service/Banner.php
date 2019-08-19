<?php
/**
 *  *  author by ben ,banner广告重构
 *   */
class Service_Banner extends RedisBase{


	private $expire_time = 300 ;
	private $banner_list = 'banner_list_%s_%s_%s';// cityid ,position , os
	public function __construct(){

		parent::__construct($mode='');
	}


	public function __CommonParamHandler($data){

		$adverturl =  Common_Const::API_URL.'/openapi/advert/index?act=transfer'; //域名这东西还是写配置比较好

		if(strpos($data['schema_link'],'type=html') !== false){
			$link = explode('url=',$data['schema_link']);
			if(!empty($link[1])){
				$links = $link[0].'url='.urlencode($adverturl.'&redirect='.urlencode($link[1]));
			}else{
				$links = $data['schema_link'];
			}
		}else{
			$links = $data['schema_link'];
		}
		return array(
			'banner_url'=>$data['pic'],
			'dest_url'=>$links,
			'rose'=>$data['rose'],
			'title'=>$data['banner_title'],
			'intro'=>$data['banner_desc'],
			'pic'=>$data['pic'],
			'link'=>$links,    
		);
	}

	/**
	 ** 图片banner ,通用
	 **/
	private function _ListFetch($cityid ,$position, $os){

		$os = $os == 'ios'?2:3;
		$key = sprintf ( $this->banner_list, $cityid ,$position,$os );
		$obj = new Rediska_Key($key);
		//if($obj->isExists()){
		//  $bannerlist = $obj->getValue();
		// return json_decode($bannerlist,true);
		//}else{
		$time = time();
		$sql = "select B.pic ,B.schema_link,B.banner_title,B.banner_desc,B.rose from bbs_banner_application as A left join bbs_banner as B on A.bannerid=B.id where A.cityid in ($cityid ,0) and A.positionid=$position and A.os in (1,$os) and A.used=1 and B.begintime < $time and B.endtime > $time order by A.cityid desc ,A.id desc limit 3";
		$list = $this->db->rawQuery($sql);
		$bannerlist = array();
		if(!empty($list)){
			foreach($list as $k=>$v){
				$bannerlist[] = $this->__CommonParamHandler($v);
			}
			$listv = json_encode($bannerlist);
			$obj->setValue($listv);
			$obj->expire($this->expire_time);
		}
		return $bannerlist;
		//}
	}

	public function Get($sessionid,$pos ,$os){

		$uid = Common_Util::GetUidFromSessionID($sessionid);
		$userObj = new User_Dao();
		$cityid = $userObj->GetUserCity($uid);
		$cityid = empty($info['cityid'])?1:$info['cityid'];
		return $this->_ListFetch($cityid ,$pos ,$os);
	}
}

