<?php


define('APPLICATION_PATH', "/home/www/wxdanmu/");
include(APPLICATION_PATH . "application/library/MysqliDb.php");

$host="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
$user="heibai";
$datebase="heibai";
$pass="resd329edpo";

#测试数据库
#$host="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
#$user="heibair";
#$datebase="heibai_dev";
#$pass="pangyongtao1104";

header("Content-Type:text/html;charset=utf8");

global $DB ;

$DB = new MysqliDb($host, $user, $pass , $datebase);

//用户三次以上接单未提交封禁用户一周

//获取用户id
function getUsers($DB) {

	$sql = "SELECT id FROM partner_minipro_user";
	return array_column($DB->rawQuery($sql), 'id');

}

//根据用户id获取此用户未提交的结束任务总数
foreach(getUsers($DB) as $user_id) {

	$now = time();
	//从任务列表中获取用户未完成任务列表
	$sql = "SELECT ut.*,t.end_time FROM partner_minipro_user_task ut";
	$sql .= " INNER JOIN partner_minipro_task t ON t.id = ut.task_id AND t.end_time < {$now}";
	$sql .= " WHERE ut.ispunished = 1 AND ut.user_id = {$user_id} AND ut.task_id NOT IN (SELECT task_id from partner_minipro_review_task where user_id={$user_id})";
	$ret = $DB->rawQuery($sql);
	if (count($ret) >= 3) { //未提交的任务的数量大于三对用户进行封禁
		$task_ids = array_column($ret,'task_id');
		$end_time_arr = array_column($ret,'end_time');
		$pos = array_search(max($end_time_arr),$end_time_arr);
		$forbidden_endtime = $end_time_arr[$pos] + 86400 * 7;//从最晚的一次任务结束时间开始算，封禁七天
		$task_ids_str = implode(',', $task_ids);
        $sql2 = "SELECT name FROM partner_minipro_task WHERE id in ({$task_ids_str})";
        $ret2 = $DB->rawQuery($sql2);
		$task_names = $ret2;
		//print_r($task_names);exit;
		$task_name_str = ''; 
		foreach (array_column($task_names,'name') as $task_name) {
			$task_name_str .= '"'.$task_name.'"、';
		}   
		$task_name_str =  rtrim($task_name_str, "、");
		//开启事务
        $DB->autocommit(false);
        $task_ids_str = implode(',', $task_ids);
        //去任务表中更新任务惩罚状态为已经惩罚
        $sql3 = "UPDATE partner_minipro_user_task set ispunished = 2 WHERE user_id = {$user_id} AND task_id IN ($task_ids_str)";
        $ut_ret = $DB->rawUpdate($sql3);

        //去用户表中修改认证状态为认证失败
        $DB->where('id',$user_id);
        $u_ret = $DB->update('partner_minipro_user',['verifystatus' => 3, 'isforbidden' => 1, 'forbidden_endtime' => $forbidden_endtime, 'forbidden_reason' => ">超过三次未完成任务，临时封禁一周"]);

        //去通知表中添加一条新纪录
        $notice_data = [ 
            'type' => 1,
            'user_id' => $user_id,
            'content' => '您在任务'.$task_name_str.'中,接单后未完成任务，现将您账户封禁7天，7天后自动解封',//此处需要列出未完成任务的三次任务名称
            'create_time' => time(),
            'publish_time' => time()
            ];  
        $n_ret = $DB->insert('partner_minipro_notice',$notice_data);
        if ($ut_ret && $u_ret && $n_ret) {
            $DB->commit();
        }   
        //回滚
        $DB->rollback();
	}

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

