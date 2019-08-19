<?php


define('APPLICATION_PATH', "/home/www/wxdanmu/");
include(APPLICATION_PATH . "application/library/MysqliDb.php");

$host="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
$user="heibai";
$database="heibai";
$pass="resd329edpo";

#测试数据库
#$host="rdsze2ynqrvnran.mysql.rds.aliyuncs.com";
#$user="heibair";
#$database="heibai_dev";
#$pass="pangyongtao1104";

header("Content-Type:text/html;charset=utf8");

global $DB;

$DB = new MysqliDb($host, $user, $pass, $database);

global $Redis;
$Redis = new \Redis();
$Redis->connect('127.0.0.1','6379');
$Redis->auth('jiangfengloveheibaixiaoyuan');


//用户接单后提醒其完成任务

//获取未结束任务id,且结束时间才当前时间的两小时内
function getTasks($DB) {

	$now_time = time();
    $sql = "SELECT id FROM partner_minipro_task";
	$sql .= " WHERE status = 1 AND start_time < {$now_time} AND end_time < ({$now_time} + 60*60*3) AND isnotice = 2";
	//print_r($DB->rawQuery($sql));exit;
    return array_column($DB->rawQuery($sql), 'id');

}

//从任务用户列表中选择接单用户，发送任务到期通知
if (empty(getTasks($DB))) exit;
foreach (getTasks($DB) as $task_id) {

	//用用户任务关联表中查找用户对应的信息(且该用户未提交过审核),则向该用户发送任务结束通知
	$sql = "SELECT ut.user_id FROM partner_minipro_user_task ut";
	$sql .= " LEFT JOIN partner_minipro_review_task rt ON (rt.user_id = ut.user_id AND rt.task_id = ut.task_id)";
	$sql .= " WHERE ut.task_id = {$task_id} AND rt.user_id IS NULL AND rt.task_id IS NULL";
	$user_ids = array_column($DB->rawQuery($sql),'user_id');

	if (!empty($user_ids)) {
		foreach ($user_ids as $user_id) {
			//向该用户发送任务结束通知
			//获取用户的的openID
			$u_sql = "SELECT openid FROM partner_minipro_user WHERE id = {$user_id}";
			$openId = $DB->rawQuery($u_sql)[0]['openid'];
			//根据用户id取出formId用于推送消息
			$formId = getFormId($openId);
			if ($formId) {
				sendNotice($formId,$openId,$task_id);
			}
		}
	}
	//更新任务表状态为已发送通知
	$DB->where('id',$task_id);
	$DB->update('partner_minipro_task',['isnotice' => 1]);
}

//取出一个可用的用户openId对应的推送码
function getFormId($openId) {
    $res = getFormIds($openId);
    if($res){
        if(!count($res)){
            return FALSE;
        }
        $newData = array();
        $result = FALSE;
        for($i = 0;$i < count($res);$i++){
            if($res[$i]['expiry_time'] > time()){
                $result = $res[$i]['formId'];//得到一个可用的formId
                for($j = $i+1;$j < count($res);$j++){//移除本次使用的formId
                    array_push($newData,$res[$j]);//重新获取可用formId组成的新数组
                }
                break;
            }
        }
        saveFormIds($openId,$newData);
        return $result;
    }else{
        return FALSE;
    }
}

function getFormIds($openId){
	
	global $Redis;
	$cacheKey = md5('user_formId'.$openId);
	$data = $Redis->get($cacheKey);
	if($data)return json_decode($data,TRUE);
	else return FALSE;

}   

function saveFormIds($openId,$data){

	global $Redis;
	$cacheKey = md5('user_formId'.$openId);
	return $Redis->setex($cacheKey,60*60*24*7,json_encode($data));

} 

/** 
* json CURL
*/
function jsonHttp($url, $data){

	$ch = curl_init();
	$header = "Accept-Charset: utf-8";
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	//curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$tmpInfo = curl_exec($ch);
	if (curl_errno($ch)) {
		return false;
	}else{
		return $tmpInfo;
	}
}


/** 
* 给客户发送消息
*/
function sendNotice($formId,$openId,$task_id) {

global $DB;
$task_sql = "SELECT name,end_time FROM partner_minipro_task WHERE id={$task_id}";
$task_info = $DB->rawQuery($task_sql)[0];
$task_name = $task_info['name'];
$task_end_time = Date('Y-m-d H:i:s', $task_info['end_time']);
$url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".getAccessToken();
$json_data = '{
	"touser":"'.$openId.'",
	"template_id":"XHlLygz123JFuzolKJW2-8FaeZYpN0XJ323kDaybRRs",
	"page": "index",
	"form_id": "'.$formId.'",
	"data": {
		"keyword1": {
			"value": "'.$task_name.'", 
			"color": "#173121"
		}, 
		"keyword2": {
			"value": "'.$task_end_time.'", 
			"color": "#173177"
		}, 
		"keyword3": {
			"value": "你的任务'.$task_name.'即将延期，请及时处理!", 
			"color": "#173177"
		}, 
		"keyword4": {
			"value": "任务即将截止", 
			"color": "#173177"
		} 
	},
	"emphasis_keyword": "keyword4.DATA" 
	}';

	 return jsonHttp($url,$json_data);

}  

/**
* 获取access_token 并进行缓存
*/
function getAccessToken() {

	global $Redis;

	$accessToken = $Redis->get('parter_access_token');
	if ($accessToken === false) {
		$appid = 'wx76997afa9db064aa';
		$secret = '84551e48971cf31aa7cd73d023d0be82';
		$tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
		$getArr=array();
		$postdata = http_build_query($getArr); //生成 URL-encode 之后的请求字符串 备用
		$options = [
			'http' => [
				'method' => 'get',
				'header' => 'Content-type:application/x-www-form-urlencoded',
				'content'=> $postdata,
				'timeout'=> 15 * 60 //超时时间 (单位:s)
			]
		];
		$context = stream_context_create($options);//创建资源流上下文 
		$result = file_get_contents($tokenUrl,false,$context);
		$tokenArr=json_decode($result,true);
		$accessToken = $tokenArr['access_token'];
		$Redis->setex('partner_access_token','3600',$accessToken);
	}
	return $accessToken;

}

