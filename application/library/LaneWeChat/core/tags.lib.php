<?php 
namespace LaneWeChat\Core;
/**
 * 新增用户标签管理
 * @author jokechat
 * 2016年9月19日 上午11:08:31
 * @email jokechat@qq.com
 */
class Tags
{
	/**
	 * @desc 获取用户标签列表
	 * @return 
	 * {"tags":[{"id":2,"name":"星标组","count":0},{"id":100,"name":"demo","count":2}]}
	 */
	public static function get()
	{
		$accessToken 	= AccessToken::getAccessToken();
		$url 			= 'https://api.weixin.qq.com/cgi-bin/tags/get?access_token='.$accessToken;
		$result 		= Curl::callWebServer($url, null, 'POST');
		return $result;
	}
	
	/**
	 * @desc 获取指定标签下的用户列表
	 * @param number $tagid 标签id
	 * @param string $next_openid 下一个用户openid
	 * @return unknown
	 */
	public static function getUserList($tagid,$next_openid = "")
	{
		$accessToken 	= AccessToken::getAccessToken();
		$url 			= "https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=".$accessToken;
		$data 			= ['tagid'=>$tagid,"next_openid"=>$next_openid];
		$result 		= Curl::callWebServer($url, json_encode($data), 'POST');
		return $result;
	}
}
?>