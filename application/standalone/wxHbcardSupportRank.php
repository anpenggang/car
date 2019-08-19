<?php
define("APPLICATION_HBCARD","/home/www/Hbcardwx/");
include(APPLICATION_HBCARD."application/library/MysqliDb2.php");
include(APPLICATION_HBCARD . "application/library/CommonUtil.php");
define('APPLICATION_PATH', "/home/www/app/misadmin/");
include(APPLICATION_PATH . "application/library/Mailerdu.php");

global $DB;
//$DB = MysqliDb2::getInstance('rdsze2ynqrvnran.mysql.rds.aliyuncs.com','heibai','resd329edpo','heibai'); 
$DB = MysqliDb2::getInstance('rdsze2ynqrvnran.mysql.rds.aliyuncs.com','heibair','pangyongtao1104','heibai'); 
//$DB = MysqliDb2::getInstance('rdsze2ynqrvnran.mysql.rds.aliyuncs.com','heibair','pangyongtao1104','heibai_dev'); 

$sql = "select * from wx_hbcard_userinfo where subscribe = 1 and total_sub>0 order by total_sub desc limit 0,50";
$ret = $DB->rawQuery($sql);

$result = "
	<table>
	<tr>
	<td> id </td>
	<td> 用户昵称 </td>
	<td> 助力值 </td>
	<td> 人气 </td>
	</tr>";
foreach($ret as $k=>$v){
	$result .= "
		<tr>
		<td>".$v['id']." </td>
		<td> ".$v['nickname']." </td>
		<td> ".$v['gold']." </td>
		<td> ".$v['total_sub']." </td>
		</tr>";
}

$result .= "</table>";

$maildesc = '关注用户人气前五十';
$content = $result;
$mail = new Mailerdu();
$filename = "wxHbcardSupportRank".date("Y-m-d H:i:s",time());
$file = '/tmp/club/'.$filename.".xls";
file_put_contents($file ,$result);
$mail->send($maildesc,$maildesc,$file,'houbaoshun');
//$mail->send($filename,$maildesc,$file,'dudu');


//die
?>
