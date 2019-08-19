<?php


define('APPLICATION_PATH', "/home/www/Indiana/");
require_once APPLICATION_PATH.'application/third/aliyun-php-sdkv2-20130815/aliyun.php';
include(APPLICATION_PATH . "application/library/MysqliDb2.php");
include(APPLICATION_PATH . "application/library/Service/QrCode.php");
include(APPLICATION_PATH . "application/library/Upload/Conf.php");
include(APPLICATION_PATH . "application/library/Upload/Image.php");
include(APPLICATION_PATH . "application/library/OssUtil.php");

$host="120.76.225.6";
$user="Indiana_w";
$datebase="Indiana";
$pass="indiana123w";

global $DB ;

$DB = MysqliDb2::getInstance($host, $user, $pass , $datebase);


//揭晓列表计算
function UserListGet($DB){

	$sql = "select id ,qrcode from userinfo where qrcode='' ";
	$list = $DB->rawQuery($sql);
	return $list;
}

function UserInfoUpdate($id,$data){
	
	global $DB;
	$DB->where('id',$id);
	$DB->update('userinfo',$data);

}

function QRcodeUrl($uid){
	
	return 'http://tdwxkj.com/?uid='.$uid ;
}

$list = UserListGet($DB);

if(!empty($list)){
	$imageObj = new Upload_Image();
	foreach($list as $user){

		$uid = $user['id'];
		$file = "/tmp/qrcode/".$uid.".png";
		$url = QRcodeUrl($uid);
		Service_QrCode::png($url,$file);

		$pickey = $imageObj->upload($file,filesize($file),'png');
		$pic = Upload_Conf::$image_domain.$pickey;
		UserInfoUpdate($uid,array('qrcode'=>$pic));
		@unlink($file);
	}
}


echo 'success';
?>
