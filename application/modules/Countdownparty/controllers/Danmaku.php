<?php

/**
 * 
 * @name DanmakuController
 * @desc Danmaku控制器,弹幕相关内容处理的控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Danmaku.php v0.0 2017/12/18 新建
 */

class DanmakuController extends BaseController {
    
	private $_model = null;
	private $user_model = null;
	
    /** 
     * 初始化方法 被调用的时候先执行初始化方法，作用域当前控制器
     */
    public function init() {
    
		parent::init();//调用父类初始化方法

		$this->_model = new CdpDanmakuModel();//初始化弹幕模型
		$this->user_model = new CdpUserModel();//初始化用户模型		

    }   

	/**
	 * 添加弹幕方法
	 */
	public function addDanmakuAction() {
		
		if ($this->getRequest()->getMethod() == 'POST') {

			//参数处理
			$args['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n','');//用户id
			$args['content'] = Common_Util::getHttpReqQuery($this,'content','Str','n');//弹幕内容
			$args['create_time'] = time();

			//如果弹幕字数大于30字直接返回报错
			if (mb_strlen($args['content'],'utf8') > 30) {
				//echo $args['content'],mb_strlen($args['content'],'utf8'); 
				return Common_Util::returnJson('10004','弹幕字数过长');
			}
	
			//判断用户是否被禁用，被禁用的话修改弹幕审核状态为9(不通过)
			if (!empty($this->user_model->isforbidden($args['user_id']))) {
				$args['status'] = 9;
			}

			$result = $this->_model->addDanmaku($args);
			if ($result) {

				return Common_Util::returnJson('20004','弹幕添加成功');

			}

			return Common_Util::returnJson('20005','弹幕写入失败');

		} else {

			return Common_Util::returnJson('10007','请求方法有误');

		}

	}

	/**
	 * 审核弹幕方法
	 */
	public function reviewDanmakuAction() {

		if ($this->getRequest()->getMethod() == 'PUT') {
			//参数处理
			$danmu_id = Common_Util::getHttpReqQuery($this,'danmu_id','Int','n','');//弹幕id
			$status = (int)Common_Util::getHttpReqQuery($this,'status','Int','n');//弹幕的审核状态 6//审核通过,9审核不通过
					
			if (!($status ==  6 || $status == 9)) {
				return Common_Util::returnJson('10005','参数类型错误');
			}

			$result = $this->_model->reviewDanmaku($danmu_id,$status);
			if (!empty($result)) {
				//弹幕审核通过后将其发送进行广播
				if($status == 6) {
				    $url = '127.0.0.1:4237/?cmd=send_to_group&group_id=10&message=DANMAKU%3A'.urlencode($result);
					$html = Common_Util::RequestHttpArray('get',$url);
					//echo $html;
				}
				return Common_Util::returnJson('20006','弹幕审核成功');
				
			} else {
				return Common_Util::returnJson('20007','弹幕审核失败');
			}
			
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}

	}
	
	/**
	 * 弹幕审核列表
	 */
	public function reviewingDanmukuListAction() {

		if ($this->getRequest()->getMethod() == 'GET') {
			$username = Yaf_Session::getInstance()->get('username');
			$user_id = explode('_',$username)[1];
			$result = $this->_model->reviewingDanmukuList($user_id);
			if (!empty($result)) {
				return Common_Util::returnJson('20001','查询成功',$result);
			}
			return Common_Util::returnJson('20002','暂时无未审核弹幕');
		} else {
			return Common_Util::returnJson('10007','请求方法错误');
		}

	}

	/**
	 * 弹幕开启状态
	 */
	public function DanmakuStatusAction() {
		
		if(time() > strtotime("2018-1-1 1:00:00")) {
			return Common_Util::returnJson('20001','活动已结束');
		} else {
			return Common_Util::returnJson('20002','活动已开启');
		}

	}

	/**
     * 生成活动二维码
     */
    public function generatingQrcodeAction() {

		//1.获取的access_token
		$appid = 'wxe32566d8be4210c7';
		$appsecret = 'b4eaf60b02a1e220e15f6b5ac783d1c2';
		$tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
		$getArr = array();
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
		$tokenArr = json_decode($result,true);
		$access_token = $tokenArr['access_token'];

		//2.生成二维码
		$path = "pages/index/index";//扫码进入的页面和携带的参数
		$width = 430; //二维码的边长
		$post_data = '{"path":"'.$path.'","width":'.$width.'}';
		$url = "https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token; //圆形接口
		$result = $this->apiNoticeIncrement($url, $post_data);
		if (empty($result)) {
			echo 123;
			return false;
		}

		header("Content-type:image/jpeg");
		echo $result;
    }

	/**
	 * 调用微信接口，生成二维码  
	 */
    public function apiNoticeIncrement($url, $data){
        
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
		//var_dump($tmpInfo);
		//exit;
		if (curl_errno($ch)) {
			return false;
		} else {
			// var_dump($tmpInfo);
			return $tmpInfo;
		}
	}

	/**
	 * 返回视频连接
	 */
	public function getVideoUrlAction() {

		$data = [
			'url' => 'https://img.stuhui.com/kuanianSD.m4v'
		];
		return Common_Util::returnJson('20001','查询成功',$data);
	}

	/**
	 * 用户导出证书
	 */
	public function certificateAction() {

		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n','');//弹幕id
		$user_info = $this->user_model->getUserInfoById($user_id);
		if (empty($user_info['dnickname']) || empty($user_info['school'])) {
			
			return Common_Util::returnJson('20002','用户未参与活动');

		}
		$filename = md5($user_info['id'].$user_info['nickname']);
		if (is_file(APPLICATION_PATH.'/public/images/CdpCertificate/'.$filename.'.png')) {

			$url = 'https://2.heibaixiaoyuan.com/public/images/CdpCertificate/'.$filename.'.png';
			return Common_Util::returnJson('20001','证书导出成功',['url' => $url]);

		}

		$ret = system("python /home/www/wxdanmu/application/modules/Countdownparty/controllers/certificate.py ${user_info['nickname']} {$filename}",$var);		
		if ($var === 0) {
			
			$url = 'https://2.heibaixiaoyuan.com/public/images/CdpCertificate/'.$filename.'.png';
			return Common_Util::returnJson('20001','证书导出成功',['url' => $url]);

		}
		
		return Common_Util::returnJson('20003','证书导出失败，请重试');
	}

	/**
	 * 用户分享时显示其为第几个参与活动的
	 */
	public function userSequenceAction() {

		$user_id = Common_Util::getHttpReqQuery($this, 'user_id', 'Int', 'n', '');//弹幕id
		
		return Common_Util::returnJson('20001','查询成功',['user_seq' => 932745 + $user_id]);

	}
}
