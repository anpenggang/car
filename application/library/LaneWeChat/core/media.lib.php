<?php
namespace LaneWeChat\Core;
/**
 * 素材管理 用来替换 多媒体的上传与下载
 * Created by Lane.
 * User: lane
 * Date: 14-8-11
 * Time: 上午9:51
 * E-mail: lixuan868686@163.com
 * WebSite: http://www.lanecn.com
 */
class Media{
    /**
     * 多媒体上传。上传图片、语音、视频等文件到微信服务器，上传后服务器会返回对应的media_id，公众号此后可根据该media_id来获取多媒体。
     * 上传的多媒体文件有格式和大小限制，如下：
     * 图片（image）: 1M，支持JPG格式
     * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：10MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * 媒体文件在后台保存时间为3天，即3天后media_id失效。
     *
     * @param $filename，文件绝对路径
     * @param $type, 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @return {"type":"TYPE","media_id":"MEDIA_ID","created_at":123456789}
     */
    public static function upload($filename, $type){
        //获取ACCESS_TOKEN
        $accessToken 	= AccessToken::getAccessToken();
        $queryUrl 		= 'http://file.api.weixin.qq.com/cgi-bin/media/upload?access_token='.$accessToken.'&type='.$type;
        $data 			= [];
        $data['media'] 	= Curl::addFile($filename);
        return Curl::callWebServer($queryUrl, $data, 'POST', 1 , 0);
    }

    /**
     * @desc 将图片上传到微信服务器  换取url(图文消息可使用该url)
     * @param string $filename  含有文件路径的图片
     * @return  ["url":"http://mmbiz.qpic.cn/mmbiz_jpg/47lKiaF8XNfuZoPjSo0OtZxkf7ziacibDc7ibA6sh68mFJ7yehPNPEo070jNdy8ibNzZUTVaibWVJsPyHibddcAphP1UQ/0"]
     */
    public static  function uploadimg($filename)
    {
    	//获取ACCESS_TOKEN
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= 'http://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token='.$accessToken.'&type=image';
    	$data 			= [];
    	$data['media'] 	= Curl::addFile($filename);
    	return Curl::callWebServer($queryUrl, $data, 'POST', 1 , 0);
    }
    
    /**
     * @desc 新增媒体文件 永久素材
     * @param string $filename
     * @param string $type 媒体文件类型，分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb）
     * @return 
     * {
		   "media_id": "UUEZ92DpUbUU3K5Y89u87zO-XZdJt1_1ovUX3pgCFSw",
		    "url": "http://mmbiz.qpic.cn/mmbiz_jpg/47lKiaF8XNfuZoPjSo0OtZxkf7ziacibDc7mBq5ak136skBgA0OpbIKCNibiajsNicaQAdtUnO3tEClShl0gJ0miaicib6w/0?wx_fmt=jpeg"
		}
     */
    public static function addMaterial($filename,$type)
    {
    	//获取ACCESS_TOKEN
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= 'https://api.weixin.qq.com/cgi-bin/material/add_material?access_token='.$accessToken.'&type='.$type;
    	$data 			= [];
    	$data['media'] 	= Curl::addFile($filename);
    	$result 		= Curl::callWebServer($queryUrl, $data, 'POST', 1 , 0);
    	return $result;
    }
    
    /**
     * @desc 根据素材id  删除微信服务器指定素材
     * @param string $media_id
     * @return {"errcode":0,"errmsg":"ok"}
     */
    public static function deleteMaterial($media_id)
    {
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=".$accessToken;
    	$data 			= ['media_id'=>$media_id];
    	$result 		= Curl::callWebServer($queryUrl,json_encode($data),"POST",true);
    	return $result;
    }
    
    /**
     * @desc 获取永久素材 
     * @author jokechat@qq.com
     * @param string $media_id 素材id
     * @return 
     * 该接口微信设计的比较扯
     * <li>如果素材是图文消息 返回json数据</li>
     * <li>如果素材是视频  返回下载地址{"title":TITLE,"description":DESCRIPTION,"down_url":DOWN_URL,}</li>
     * <li>如果是其他类型的素材消息，则响应的直接为素材的内容 自行保存文件</li>
     * <li>错误返回 {"errcode":40007,"errmsg":"invalid media_id"}</li>
     */
    public static function getMaterial($media_id)
    {
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=".$accessToken;
    	$data 			= ['media_id'=>$media_id];
    	$result 		= Curl::callWebServer($queryUrl,json_encode($data),"POST",0);
    	return $result;
    }
    
    /**
     * @desc 获取永久各类型素材总数目
     * @author jokechat@qq.com
     * @return {"voice_count":0,"video_count":0,"image_count":23,"news_count":11}
     */
    public static function getMaterialCount()
    {
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=".$accessToken;
    	$data 			= [];
    	$result 		= Curl::callWebServer($queryUrl,$data,"POST",1,0);
    	return $result;
    }
    
    /**
     * @desc 获取素材类型的素材列表 素材链接仅限在腾讯系域名使用
     * @author jokechat@qq.com
     * @param string $type 素材的类型，图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param number $offset 从全部素材的该偏移位置开始返回，0表示从第一个素材 返回
     * @param number $count 返回素材的数量，取值在1到20之间
     * @return 返回素材列表
     */
    public static function getMaterialList($type,$offset=0,$count=20)
    {
    	$accessToken 	= AccessToken::getAccessToken();
    	$queryUrl 		= "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=".$accessToken;
    	$data 			= ['type'=>$type,'offset'=>$offset,'count'=>$count];
    	$result 		= Curl::callWebServer($queryUrl,json_encode($data),"POST",true,0);
    	return $result;
    }
    
}
