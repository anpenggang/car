<?php

/**
 *
 * @name InviteController
 * @desc 邀请控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Invite.php v0.0 2018/3/12 新建
 */
class InviteController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化用户类型
		$this->_model = new PtnUserModel();
		
		//根据sessionid获取用户id
		$this->_userinfo = $this->verifySessionid($this);

	}
	
	/**
	 * 获取邀请二维码(带海报)
	 */
	public function allGetQRcodeAction() {
			
		//1.获取access_token
		$access_token = $this->getAccessToken();
		
		//2.生成二维码
		//无限生成接口
		$path="pages/index/index";//扫码进入的页面
		$scene=$this->_userinfo['user_id'];//携带的参数(推荐人用户id)
		$width=500;
        $post_data='{
            "page": "'.$path.'",
            "width": "'.$width.'",
            "scene": "'.$scene.'"
        }';
		$post_url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token; //无限生成接口  
		//$result = $this->jsonHttp($post_url,$post_data);
		$result = Common_Util::RequestHttpJson($post_url, $post_data);
		//缩放二维码大小为需要的大小，并将二维码加入到海报中
        $thumb = imagecreatetruecolor(500, 500);//创建一个360x360图片，返回生成的资源句柄
        //获取源文件资源句柄。接收参数为图片流，返回句柄
        $source = imagecreatefromstring($result);
        //将源文件剪切全部域并缩小放到目标图片上，前两个为资源句柄
        imagecopyresampled($thumb, $source, 0, 0, 0, 0, 500, 500, 500, 500);
        //创建图片的实例，接收参数为图片
        $dst_qr = imagecreatefromstring(file_get_contents(APPLICATION_PATH."/public/partner_poster.png"));//海报底图
        //加水印
        imagecopy($dst_qr, $thumb, 320, 790, 0, 0, 500, 500);
        //销毁
        imagedestroy($thumb);

        ob_start();//启用输出缓存，暂时将要输出的内容缓存起来
        imagejpeg($dst_qr, NULL, 100);//输出
        $poster = ob_get_contents();//获取刚才获取的缓存
        ob_end_clean();//清空缓存
        imagedestroy($dst_qr);	

		header("Content-type:image/jpeg");
		echo $poster;
		
	}

	/** 
     * 调用微信接口，生成二维码  
     */
    private function jsonHttp($url, $data){

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
	 * 生成的二维码上传到阿里云
	 */
	public function addTextWatermarkAction () {

		#$base_url = "http://fe3.stuhui.com";
		$base_url = "https://2.heibaixiaoyuan.com";
		$user_model = new PtnUserModel();
		$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);
		if ($user_info['realname'] === '') {
			return Common_Util::returnJson('10004','请先去完善资料');
		}
        $filename = md5($user_info['id'].$user_info['nickname']);
		if ($user_info['invite_url'] != '') {
			return Common_Util::returnJson('20001','海报导出成功',['url' => $user_info['invite_url']]);
		}

		$poster_url = $base_url."/Partner/Invite/allGetQRcode?sessionid=".Common_Util::getHttpReqQuery($this,'sessionid','Str','n');
        $ret = passthru("python /home/www/wxdanmu/application/modules/Partner/controllers/Invite.py '${user_info['nickname']}' '{$filename}' '{$poster_url}'",$var);
        if ($var === 0) {
			//将本地文件上传到文件服务器，并将url存储到本地

			$objmodel = new ObjectModel();
			$filedir = '/home/www/wxdanmu/public/images/partnerInvite/'.$filename.'.png';
			$object_key = $objmodel->createPicObject($filedir, filesize($filedir), $filename);
			list($key, $bucket) = explode("|", $object_key);
			$prefix = 'http://img.stuhui.com';
			$imgsrc = "$prefix/$key";
			$update_ret = $user_model->UpUserinfo(['invite_url' => $imgsrc],$user_info['id']);
			if ($update_ret) {
				if (is_file($filedir)) {
            		unlink($filedir);
				}
            	return Common_Util::returnJson('20001','海报导出成功',['url' => $imgsrc]);
			}

        }   
    
        return Common_Util::returnJson('20003','海报导出失败，请重试');

	}

	/**
	 * 获取我邀请的人和邀请奖励
	 */
	public function getMyInviteRewardAction() {

		$count = $this->_model->getMyInviteCount($this->_userinfo['user_id']);
		$bill_detail_model = new PtnBillDetailModel();
		$my_invite_income = $bill_detail_model-> getInviteIncome($this->_userinfo['user_id']);

		$data = [
			'inviters' => $count[0]['count'],
			'invite_income' => $my_invite_income['income'],
			'invite_ap' => 0
		];

		return Common_Util::returnJson('20001','查询成功',$data);

	}

	/**
	 * 获取我邀请的人列表
	 */
	public function getMyInviteAction() {
		
		$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//第几页
		$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页数量

		if (!($page&&$size)) {
			$page = 1;
			$size = 12; 
		} 
 
		$invite_list = $this->_model->getMyInvite($this->_userinfo['user_id'],$page,$size);
		if (empty($invite_list)) {
			return Common_Util::returnJson('20002','暂无数据');
		}
		$count = $this->_model->getMyInviteCount($this->_userinfo['user_id']);
		$data = [
			'item' => $invite_list,
			'curPage' => $page,
			'curSize' => $size,
			'count' => $count[0]['count'],
		];
		return Common_Util::returnJson('20001','查询成功',$data);

	}

	/**
	 * 获取access_token 并进行缓存
	 */
	private function getAccessToken() {

		$accessToken = $this->_redis->get('parter_access_token');
		if ($accessToken === false) {
			$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
			$tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$config->partner->appid."&secret=".$config->partner->appsecret;
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
			$this->_redis->setex('partner_access_token','3600',$accessToken);
        }   
        return $accessToken;
	}


}//class
