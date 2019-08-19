<?php


define('APPLICATION_PATH', "/home/www/wxdanmu/");
include(APPLICATION_PATH . "application/library/MysqliDb.php");

$host="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
$user="heibai";
$database="heibai";
$pass="resd329edpo";

#测试数据库
$host_test="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
$user_test="heibair";
$database_test="heibai_dev";
$pass_test="pangyongtao1104";

header("Content-Type:text/html;charset=utf8");

global $DB,$DB_TEST;

$DB = new MysqliDb($host, $user, $pass, $database);
$DB_TEST = new MysqliDb($host_test, $user_test, $pass_test, $database_test);

//获取马甲库用户资料
function getSockpuppetUser($DB) {

	$sql = "SELECT am.username,u.icon_b,u.userid FROM actuser_majia am";
	$sql .= " LEFT JOIN userinfo u ON am.userid=u.userid ";
	$sql .= "LIMIT 10000";
	return $DB->rawQuery($sql);
}

//向用户表中插入马甲用户数据
foreach (getSockpuppetUser($DB) as $userinfo) {

	$data = [
		'origin' => 2,
		'nickname' => $userinfo['username'],
		'avatar_url' => $userinfo['icon_b'],
		'openid' => $userinfo['userid'],
		'create_time' => time()
	];
	$DB_TEST->insert('lecture_user',$data);

} 
