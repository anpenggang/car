<?php

/**
 * @name ActivityController
 * @desc 活动控制器
 * @Author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Activity.php 0.0 2017/10/10 创建
 * @version Activity.php 0.0 2017/11/22 新增生成海报方法 allGeneratingQrcodeAction
 * @version Activity.php 0.1 2018/1/16  新增addActivity2Action updateActivity2Action (作为其原本方法的测试版)
 */
class ActivityController extends BaseController {

	private $_model = null; //活动模型

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		//实例化ActivityModel
		$this->_model = new ActivityModel();

	}
	
	/**
	 * PC端登录接口
	 */
	public function loginDanmakuAction() {

		//参数设置
		$roomnum = Common_Util::getHttpReqQuery($this,'roomnum','Str','n');
		$secret_key = Common_Util::getHttpReqQuery($this,'secret','Str','n');
		$result = $this->_model->loginDanmaku($roomnum,$secret_key);
		if (!empty($result)) {
			//判断是否有屏幕在线，在线的话是否设置单登录
			//$redis = new \Redis();
			//$redis->connect('127.0.0.1','6379');
			//$redis->auth('jiangfengloveheibaixiaoyuan');
			$screen_status = $this->_redis->get($roomnum);
			if ((int)$screen_status == 9) {
				return Common_Util::returnJson('10004','屏幕已在线，不允许重复登录');
			}
			return Common_Util::returnJson('20001','登录成功',$result[0]);
		}
		return Common_Util::returnJson('20002','验证错误');
		
	}

	/**
	 * 添加活动
	 */
	public function addActivityAction() {
	
		//参数处理
		$data = [];
		$data['name'] = Common_Util::getHttpReqQuery($this,'name','Str','n');		//活动名称
		$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n');	//活动负责人（创建者）
		$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n'); //活动弹幕字体样式
		$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n');//活动弹幕默认颜色
		$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Str','n');//活动弹幕字体大小
		$data['bar_speed'] = Common_Util::getHttpReqQuery($this,'bar_speed','Int','n');//活动弹幕的移动速度
		$data['loop_play'] = Common_Util::getHttpReqQuery($this,'loop_play','Int','n');//活动弹幕是否循环播放
		$data['monopoly'] = Common_Util::getHttpReqQuery($this,'monopoly','Int','n');//活动是否开启霸屏功能	
		//$data['show_nickname'] = Common_Util::getHttpReqQuery($this,'show_nickname','Int','n');//是否显示用户昵称
		$data['create_time'] = time();//活动创建时间
		//$data['roomnum'] = Common_Util::getHttpReqQuery($this,'roomnum','Int','n','');//弹幕墙编号
		$data['secret_key'] = $this->generatePassword(6);	//生成活动密钥

		
		//判断该用于创建的活动数目，如果大于三个，则提示其不能继续创建
		$create_act_num = $this->_model->getCreateActNum($data['user_id']);
		if ($create_act_num >= 3) {
			return Common_Util::returnJson('20005','创建活动数大于3');
		}

		//对房间号进行维护
		$roomnum = $this->_model->getRoomnum();
		if (empty($roomnum)) {
			//如果房间编号表中没有记录，则插入一条作为房间编号
			$roomnum = $this->_model->addRoomnum();
			$data['roomnum'] = $roomnum;	
		} else {
			$data['roomnum'] = $roomnum[0]['id'];
		}

		$result = $this->_model->addAct($data);
		if (is_numeric($result) && (0 < $result)) {
			return Common_Util::returnJson('20004','创建活动成功',['act_id' => $result]);
		}
		return Common_Util::returnJson('20005','创建活动失败');

	}

	/**
	 * 添加活动
	 */
	public function addActivity2Action() {
	
		//参数处理
		$data = [];
		$data['name'] = Common_Util::getHttpReqQuery($this,'name','Str','n');		//活动名称
		$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n');	//活动负责人（创建者）
		$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n'); //活动弹幕字体样式
		$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n');//活动弹幕默认颜色
		$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Str','n');//活动弹幕字体大小
		$data['bar_speed'] = Common_Util::getHttpReqQuery($this,'bar_speed','Int','n');//活动弹幕的移动速度
		$data['loop_play'] = Common_Util::getHttpReqQuery($this,'loop_play','Int','n');//活动弹幕是否循环播放
		$data['monopoly'] = Common_Util::getHttpReqQuery($this,'monopoly','Int','n');//活动是否开启霸屏功能	
		$data['show_nickname'] = Common_Util::getHttpReqQuery($this,'show_nickname','Int','n');//是否显示用户昵称
		$data['create_time'] = time();//活动创建时间
		//$data['roomnum'] = Common_Util::getHttpReqQuery($this,'roomnum','Int','n','');//弹幕墙编号
		$data['secret_key'] = $this->generatePassword(6);	//生成活动密钥

		
		//判断该用于创建的活动数目，如果大于三个，则提示其不能继续创建
		$create_act_num = $this->_model->getCreateActNum($data['user_id']);
		if ($create_act_num >= 3) {
			return Common_Util::returnJson('20005','创建活动数大于3');
		}

		//对房间号进行维护
		$roomnum = $this->_model->getRoomnum();
		if (empty($roomnum)) {
			//如果房间编号表中没有记录，则插入一条作为房间编号
			$roomnum = $this->_model->addRoomnum();
			$data['roomnum'] = $roomnum;	
		} else {
			$data['roomnum'] = $roomnum[0]['id'];
		}

		$result = $this->_model->addAct($data);
		if (is_numeric($result) && (0 < $result)) {
			return Common_Util::returnJson('20004','创建活动成功',['act_id' => $result]);
		}
		return Common_Util::returnJson('20005','创建活动失败');

	}

	/**
	 * 根据活动ID 获取指定的活动
	 */
	public function getActivityByIdAction() {
		
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');
		$result = $this->_model->getActivityById($act_id);
		if (empty($result)) {
			return Common_Util::returnJson('20002','暂无此活动对应的信息');
		}
		return Common_Util::returnJson('20001','查询成功',$result);

	}


	/**
	 * 修改活动
	 */
	public function updateActivityAction() {
	
		//参数处理
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n'); //活动ID
		$data['name'] = Common_Util::getHttpReqQuery($this,'name','Str','n','');		//活动名称
		//$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n');	//活动负责人（创建者）
		$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n'); //活动弹幕字体样式
		$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n');//活动弹幕默认颜色
		$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Str','n');//活动弹幕字体大小
		$data['bar_speed'] = Common_Util::getHttpReqQuery($this,'bar_speed','Int','n');//活动弹幕的移动速度
		$data['loop_play'] = Common_Util::getHttpReqQuery($this,'loop_play','Int','n');//活动弹幕是否循环播放
		$data['monopoly'] = Common_Util::getHttpReqQuery($this,'monopoly','Int','n');//活动是否开启霸屏功能	
		$data['modify_time'] = time();//活动修改时间
		
		$result = $this->_model->updateAct($data);
		if ($result === true) {
			//修改成功时候将新样式发送到PC端，做PC端热处理
			$styledata['font_family'] = $data['font_family'];
			$styledata['font_color'] = $data['font_color'];
			$styledata['font_size'] = $data['font_size'];
			$styledata['bar_speed'] = $data['bar_speed'];
			//获取房间编号，用于向该房间的屏幕发送消息
			$roomnum = $this->_model->getRoomnumById($data['act_id']);
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum['roomnum'].'&message=MODIFY:'.json_encode($styledata);
            $html = Common_Util::RequestHttpArray('get',$url);
			return Common_Util::returnJson('20006','修改活动成功',['act_id' => $data['act_id']]);			
		}
		return Common_Util::returnJson('20007','修改活动失败');
	}

	/**
	 * 修改活动
	 */
	public function updateActivity2Action() {
	
		//参数处理
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n'); //活动ID
		$data['name'] = Common_Util::getHttpReqQuery($this,'name','Str','n','');		//活动名称
		//$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n');	//活动负责人（创建者）
		$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n'); //活动弹幕字体样式
		$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n');//活动弹幕默认颜色
		$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Str','n');//活动弹幕字体大小
		$data['bar_speed'] = Common_Util::getHttpReqQuery($this,'bar_speed','Int','n');//活动弹幕的移动速度
		$data['loop_play'] = Common_Util::getHttpReqQuery($this,'loop_play','Int','n');//活动弹幕是否循环播放
		$data['monopoly'] = Common_Util::getHttpReqQuery($this,'monopoly','Int','n');//活动是否开启霸屏功能	
		$data['show_nickname'] = Common_Util::getHttpReqQuery($this,'show_nickname','Int','n');//是否显示用户昵称
		$data['modify_time'] = time();//活动修改时间
		
		$result = $this->_model->updateAct($data);
		if ($result === true) {
			//修改成功时候将新样式发送到PC端，做PC端热处理
			$styledata['font_family'] = $data['font_family'];
			$styledata['font_color'] = $data['font_color'];
			$styledata['font_size'] = $data['font_size'];
			$styledata['bar_speed'] = $data['bar_speed'];
			//获取房间编号，用于向该房间的屏幕发送消息
			$roomnum = $this->_model->getRoomnumById($data['act_id']);
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum['roomnum'].'&message=MODIFY:'.json_encode($styledata);
            $html = Common_Util::RequestHttpArray('get',$url);
			return Common_Util::returnJson('20006','修改活动成功',['act_id' => $data['act_id']]);			
		}
		return Common_Util::returnJson('20007','修改活动失败');
	}
	
	/**
	 * 生成房间编号房间编号
	 */
	public function getRoomnumAction() {
		
		//验证登录状态
		//$sessionid = Common_Util::getHttpReqQuery($this,'sessionid','Str','n','');//sessionid
		//$loginstatus = Common_Util::VerifySessionID($sessionid);
		//if ($loginstatus == false) {
		//	return Common_Util::returnJson('10004','sessionid验证失败...');
		//}

		$result = $this->_model->getRoomnum();
		if (!empty($result)) {
			$data = ['roomnum'=>$result[0]['id']];
			return Common_Util::returnJson('20001','查询成功',$data);
		}
		//如果没有房间编号 则向房间编号记录表中插入一条
		$roomnum = $this->_model->addRoomnum();
		$data = ['roomnum' => $roomnum];
		return Common_Util::returnJson('20001','查询成功',$data);
	}

	/**
	 * 查询我创建的活动
	 */
	public function getMyCreateActivityAction() {

		//参数设置
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n',''); //用户ID
		$result = $this->_model->getMyCreateActivity($user_id);
		
		//对获取到的数据加一个status_control字段，其值与status完全一致
		$data = [];
		foreach ($result as $key => $value) {
			$value['state_control'] = $value['status'];
			$data[] = $value;
		}

		if (empty($result)) {
			return Common_Util::returnJson('20002','无记录');	
		}
		return Common_Util::returnJson('20001','请求成功',$data);
		
	}

	/**
	 * 添加活动所对应敏感词
	 */
	public function addFilterWordAction() {

		//参数设置
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n',''); //活动ID
		$data['filter_word'] = Common_Util::getHttpReqQuery($this,'filter_word','Str','n','');//敏感词

		$result = $this->_model->addFilterWord($data);//调用model里面方法处理逻辑 可以改到C层

	}

	/**
	 * 开启或者关闭弹幕审核
	 */
	public function updateActivityCheckAction() {
		
		//参数设置
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		$bar_check = Common_Util::getHttpReqQuery($this,'bar_check','Int','n','');//审核状态 0表示开启 1，表示关闭
		if ($bar_check != 0 && $bar_check != 1) {
			return Common_Util::returnJson('10001','不支持的状态码');
		}
		
		$result = $this->_model->updateActivityCheck($act_id,$bar_check);
		if ($result == true) {
			return Common_Util::returnJson('20006','修改审核状态成功',['act_id' => $act_id]);
		}
		return Common_Util::returnJson('20007','修改审核状态失败');	

	}

	/**
	 * 获取活动所对应与的敏感词
	 */
	public function getFilterWordsAction() {

		//参数设置
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		$ret = $this->_model->getFilterWords($act_id);
		if (empty($ret)) {
			return Common_util::returnJson('20002','没有活动对应的敏感词');
		}
		return Common_Util::returnJson('20001','查询成功',$ret);

	}
	
	/**
	 * 删除活动敏感词 将关联表中的status改为9
	 */
	public function delFilterWordAction() {
	
		//参数设置
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		$filter_word_id = Common_Util::getHttpReqQuery($this,'filter_word_id','Int','n','');//敏感词ID
		$result = $this->_model->delFilterWord($act_id,$filter_word_id);
		if($result == false) {
			return Common_Util::returnJson('20007','敏感词删除出错');
		}	
		return Common_Util::returnJson('20006','敏感词删除成功');

	}

	/**
	 * 修改活动状态
	 */
	public function updateActivityStatusAction() {

		//参数处理
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		$status = Common_Util::getHttpReqQuery($this,'status','Int','n','');//活动状态

		if ($status != 0 && $status != 1 && $status != 2 && $status != 3) {
			return Common_Util::returnJson('10001','不支持的状态码');
		}
		$result = $this->_model->updateActStatus($act_id,$status);
		if ($result == true) {	
			return Common_Util::returnJson('20006','修改活动状态成功');
		}
		return Common_Util::returnJson('20007','修改活动状态失败');
		
	}
	
	/**
	 * 生成活动二维码
	 */
	public function generatingQrcodeAction() {

		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		//1.获取的access_token
		$access_token = $this->getAccessToken();
		/**
		 * $config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		 * $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$config->wx->appid."&secret=".$config->wx->appsecret;
    	 * $getArr=array();
		 * $postdata = http_build_query($getArr); //生成 URL-encode 之后的请求字符串 备用
		 * $options = [
		 * 	'http' => [
		 * 		'method' => 'get',
		 * 		'header' => 'Content-type:application/x-www-form-urlencoded',
		 * 		'content'=> $postdata,
		 * 		'timeout'=> 15 * 60 //超时时间 (单位:s)
		 * 	]
		 * ];
		 * $context = stream_context_create($options);//创建资源流上下文 
		 * $result = file_get_contents($tokenUrl,false,$context);
    	 * $tokenArr=json_decode($result,true);
		 * $access_token = $tokenArr['access_token'];
		 */
		
		//2.生成二维码
		
		//有次数限制的二维码接口
		$path="pages/new/new";//扫码进入的页面和携带的参数
    	$width=430; //二维码的边长
    	$post_data='{"path":"'.$path.'","width":'.$width.'}';
    	//$url="https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token; //圆形接口
    	$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token; //方形接口
		
		//无限生成接口
		//$path="pages/send-barrage/send-barrage";//扫码进入的页面和携带的参数
		//$scene=$act_id;//活动ID，用于进入页面后使用
		//$width=430;
    	//$post_data=[
		//	'page'  => $path,
		//	'width' => $width,
		//	'scene' => $scene
		//];
		//$post_data = json_encode($post_data);
    	//$url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token; //无限生成接口	
    	$result=$this->apiNoticeIncrement($url,$post_data);    	
		if(empty($result)) {
			echo 123;
			return false;
		}
		//$saveqrcode = $this->updateQrcode($act_id, $result);//上传二维码到服务器
		//if ($saveqrcode) {
			header("Content-type:image/jpeg");
			echo $result;
		//}
		//return Common_Util::returnJson('10004',"生成二维码失败，请重新尝试");	
	}

	/**
	 * 获取access_token，并进行缓存
	 */
	private function getAccessToken() {

		//判断是否有屏幕在线，在线的话是否设置单登录
		//$redis = new \Redis();
		//$redis->connect('127.0.0.1','6379');
		//$redis->auth('jiangfengloveheibaixiaoyuan');
		$accessToken = $this->_redis->get('wxdanmu_access_token');
		if ($accessToken === false) {
			//获取的access_token
			$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
			$tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$config->wx->appid."&secret=".$config->wx->appsecret;
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
			$this->_redis->setex('wxdanmu_access_token','3600',$accessToken);
        	//$redis->get('wx_access_token123');
		}	

        //$redis->close(); 
		return $accessToken;

	}

	/**
	 * 生成活动二维码海报
	 */
	public function allGeneratingQrcodeAction() {

		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		//1.获取的access_token
		$access_token = $this->getAccessToken();
		/**
		 * $config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		 * $tokenUrl="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$config->wx->appid."&secret=".$config->wx->appsecret;
    	 * $getArr=array();
		 * $postdata = http_build_query($getArr); //生成 URL-encode 之后的请求字符串 备用
		 * $options = [
		 * 	'http' => [
		 * 		'method' => 'get',
		 *		'header' => 'Content-type:application/x-www-form-urlencoded',
		 *		'content'=> $postdata,
		 * 		'timeout'=> 15 * 60 //超时时间 (单位:s)
		 *	]
		 * ];
		 * $context = stream_context_create($options);//创建资源流上下文 
		 * $result = file_get_contents($tokenUrl,false,$context);
    	 * $tokenArr=json_decode($result,true);
		 * $access_token = $tokenArr['access_token'];
		 */

		//2.生成二维码
		
		//有次数限制的二维码接口
		//$path="pages/send-barrage/send-barrage?act_id".$act_id;//扫码进入的页面和携带的参数
    	//$width=430; //二维码的边长
    	//$post_data='{"path":"'.$path.'","width":'.$width.'}';
    	//$url="https://api.weixin.qq.com/wxa/getwxacode?access_token=".$access_token; //圆形接口
    	//$url="https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$access_token; //方形接口
		
		//无限生成接口
		$path="pages/send-barrage/send-barrage";//扫码进入的页面和携带的参数
		$scene=$act_id;//活动ID，用于进入页面后使用
		$width=430;
    	$post_data='{
			"page": "'.$path.'",
			"width": "'.$width.'",
			"scene": "'.$scene.'"
		}';
		//$post_data = json_encode($post_data);
    	$url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$access_token; //无限生成接口	
    	$result=$this->apiNoticeIncrement($url,$post_data);    	
		if(empty($result)) {
			return Common_Util::returnJson('10004',"生成二维码失败，请重新尝试");	
		}	

		//缩放二维码大小为需要的大小，并将二维码加入到海报中
    	$thumb = imagecreatetruecolor(360, 360);//创建一个360x360图片，返回生成的资源句柄
		//获取源文件资源句柄。接收参数为图片流，返回句柄
   		$source = imagecreatefromstring($result);
		//将源文件剪切全部域并缩小放到目标图片上，前两个为资源句柄
    	imagecopyresampled($thumb, $source, 0, 0, 0, 0, 360, 360, 430, 430);
		//创建图片的实例，接收参数为图片
    	$dst_qr = imagecreatefromstring(file_get_contents(APPLICATION_PATH."/public/poster.png"));//海报底图
		//var_dump($dst_qr);
		//加水印
    	imagecopy($dst_qr, $thumb, 200, 470, 0, 0, 360, 360);
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
	 * 将生成的二维码图片保存到本地
	 */
	public function updateQrcode($act_id, $result) {
		
		//1.设置上传路径
		$path = 'public/images/qrcode/';
		$path .= date('Y/m/d', time());
		if (!file_exists($path)){
			//文件夹不存在的话，先生成文件夹
			mkdir($path,0777,true);
		}
		//2.保存文件
		$file_name = date('YmdHis', time()).mt_rand(100000,999999).'.jpg';
		if (file_put_contents($path."/".$file_name,$result)){
			//3 向数据库里面写入该文件路径
			$qrcode_path = 'https://2.heibaixiaoyuan.com/'.$path.'/'.$file_name;
			$result = $this->_model->updateQrcode($act_id,$qrcode_path);
			if ($result) {
				return true;
			} else {
				Common_Util::returnJson('10004','获取二维码出错，请重试');
				exit;
			}
		}
		return false;

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
		}else{
			// var_dump($tmpInfo);
			return $tmpInfo;
		}
	}	
	
	/**
	 * 活动敏感词过滤开关 如果敏感词过滤状态为关，则不用对界面进行验证
	 */
	public function filterStatusAction() {

		//参数设置
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');//活动ID
		$filter_status = Common_Util::getHttpReqQuery($this,'filter_status','Int','n','');//过滤开关 0关闭 1开启
		if ($filter_status != 0 && $filter_status != 1) {
			return Common_Util::returnJson('10004','开关状态值错误');
		}
		$result = $this->_model->filterStatus($act_id,$filter_status);
		if ($result === true) {
			return Common_Util::returnJson('20006','修改活动状态成功');
		}
		return Common_Util::returnJson('20007','修改活动状态失败');

	}

}
