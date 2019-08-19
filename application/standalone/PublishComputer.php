<?php


define('APPLICATION_PATH', "/home/www/Indiana/");
include(APPLICATION_PATH . "application/library/MysqliDb2.php");

$host="120.76.225.6";
$user="Indiana_w";
$datebase="Indiana";
$pass="indiana123w";

global $DB ;

$DB = MysqliDb2::getInstance($host, $user, $pass , $datebase);


//揭晓列表计算
function PublishListGet($DB){

	$sql = "select id ,goodsid ,phase from publish where userid='' ";
	$list = $DB->rawQuery($sql);
	return $list;
}


function LotteryNumberGet(){

	$url = 'http://c.apiplus.net/newly.do?token=8f9e4374b998dcab&code=cqssc&rows=1&format=json';
	$out = Request('get',$url,'');

	$out = json_decode($out,true);
	$data = $out['data'];
	$lottery = str_replace(',','',$data[0]['opencode']);
	return array('lottery'=>$lottery,'lotteryphase'=>$data[0]['expect']);
}


function LuckNumberComputer($lottery,$total){

	$luck = (999999+$lottery)%$total + 10000001;
	return $luck;
}

function GoodsInfoGet($DB,$goodsid){

	$sql = "select id ,personlimit from goods where id='$goodsid' limit 1";
	$ret = $DB->rawQuery($sql);
	return empty($ret)?array():$ret[0];
}


function Request($method, $url, $data){

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	if("post" == $method){
		curl_setopt($ch, CURLOPT_POST, 1);
		$encoded = '';
		if(!empty($data) && is_array($data)){
			foreach($data as $name => $value){
				$encoded .= urlencode($name).'='.urlencode($value).'&';
			}
			curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
		}
	}
	$output = curl_exec($ch);
	curl_close($ch);
	return $output;
}

$list = PublishListGet($DB);
if(!empty($list)){

	$lottery = LotteryNumberGet();
	foreach($list as $k){
		$goodsid = $k['goodsid'] ;
		$phase = $k['phase'];
		$info = GoodsInfoGet($DB,$goodsid);
		if(!empty($info)){
			$luck = LuckNumberComputer($lottery['lottery'],$info['personlimit']);
			$sql = "select userid ,orderid from lucknum where number='$luck' and goodsid='$goodsid' and phase='$phase' limit 1";
			$ret = $DB->rawQuery($sql);
			$userid = $ret[0]['userid'];
			$orderid = $ret[0]['orderid'];

			$sql = "select count(id) as num from lucknum where goodsid='$goodsid' and phase='$phase' and userid='$userid' ";
			$rst = $DB->rawQuery($sql);
			$usernum = $rst[0]['num'];

			$publish = array(
					'userid'=>$ret[0]['userid'],
					'lucknum'=>$luck,
					'lottery'=>$lottery['lottery'],
					'lotteryphase'=>$lottery['lotteryphase'],
					'usernum'=>$usernum,
					);
			$DB->where('id',$k['id']);
			$DB->update('publish',$publish);

			$DB->where('orderid',$orderid);
			$DB->update('orders',array('lucknum'=>$luck));
		}

	}
}



?>
