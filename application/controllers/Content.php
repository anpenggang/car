<?php

/**
 *
 * @name ContentController
 * @desc 弹幕内容控制器 
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Content.php v0.0 2017/9/30 新建 
 * 					添加分页功能，对用户发送弹幕页面进行分页处理
 */
class ContentController extends BaseController {

	private $_model = null; //弹幕内容模型

	/**
	 * 初始化方法 控制器被调用的时候先调用初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		//实例化ContentModel
		$this->_model = new ContentModel();

	}
	
	/**
	 * 查询弹幕 用于审核
	 */
	public function showBarrageContentAction() {
		
		//参数处理
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');		//活动ID
		$data['check_status'] = Common_util::getHttpReqQuery($this,'check_status','Int','n','');		//弹幕状态

		$result = $this->_model->showBarrageContent($data);
		if (empty($result)) {
			return Common_Util::returnJson('20002','暂无未审核弹幕');
		}
		return Common_Util::returnJson('20001','查询成功成功',$result);

	}	

	/**
	 * 显示某个用户在某次活动中发送的弹幕
	 */
	public function showBarrageContentMySendAction() {

		//参数处理
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n'); //活动id
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n'); //活动id

		$result = $this->_model->showBarrageContentMySend($act_id,$user_id);
		if (empty($result)) {
			return Common_Util::returnJson('20002','该用户在活动中暂时未发送弹幕');
		}
		return Common_Util::returnJson('20002','查询成功',$result);

	}
	
	/**
	 * 显示某个用户在某次活动中发送的弹幕，带分页
	 */
	public function showBarrageContentMySendWithPageAction() {

		//参数处理
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n'); //活动id
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n'); //用户id
		$page = Common_Util::getHttpReqQuery($this,'page','Int','y',1);//第几页
		$size = Common_Util::getHttpReqQuery($this,'size','Int','y',12);//每页数量

		if (!($page&&$size)) {
			$page = 1;
			$size = 12;
		}
		$result = $this->_model->showBarrageContentMySendWithPage($act_id,$user_id,$page,$size);
		if (empty($result)) {
			return Common_Util::returnJson('20002','该用户在活动中暂时未发送弹幕,或者分页数据不对');
		}
		$user_model = new UserModel();
		$user_info = $user_model->getUserInfo($user_id);
		foreach ($result as $key => $value) {
			$result[$key]['nickname'] = $user_info[0]['nickname'];//向返回数组中添加用户昵称
			$result[$key]['create_time'] = date('H:i:s', $result[$key]['create_time']);//格式化输出时间
		}
		$data['maxNum'] = $this->_model->barrageContentMySendAll_num($act_id,$user_id);//总数据量
		$data['curPage'] = $page;//第几页
		$data['curSize'] = $size;//每页显示多少条
		$data['item'] = $result;//内容
		return Common_Util::returnJson('20001','查询成功',$data);

	}

	/**
	 * 添加普通弹幕
	 */
	public function addBarrageContentAction() {

		//参数处理
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n',''); 		//活动id
		$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n',''); 		//用户id
		$data['content'] = Common_Util::getHttpReqQuery($this,'content','Str','n',''); 		//弹幕内容
		$data['content'] = str_replace(PHP_EOL,'', $data['content']);
		//$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n',''); 	//弹幕字体
		//$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n',''); 	//弹幕颜色
		//$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Int','n',''); 	//弹幕字体大小
		$data['create_time'] = time(); //弹幕创建时间

		// 判断活动是否开启，没开启的话不允许发送弹幕
		$activity_model = new ActivityModel();
		$manager = $activity_model->getActManagerById($data['act_id']);
		if($manager['status'] != 1) {
			return Common_Util::returnJson('10004','活动未开启，不能发弹幕');
		}

		//判断屏幕是否在线，不在线的话直接返回，不让其发弹幕		
		$roomnum_data = $activity_model->getRoomnumById($data['act_id']);//根据活动id获取房间编号
		$screen_status = $this->_redis->get($roomnum_data['roomnum']);//获取房间号对应的屏幕是否在线
		if ((int)$screen_status != 9) {
			return Common_Util::returnJson('10004','屏幕不在线，无法发弹幕');
		}

		//判断弹幕长度大于50字的返回错误
		if (mb_strlen($data['content'],'utf-8') > 30){
			return Common_Util::returnJson('10001','弹幕长度大于30个字');
		}
		
		//判断该用户是否被禁用,如果禁用的话,修改其发布弹幕的状态为审核不通过 2
		$user_model = new UserModel();
		$forbidden_status = $user_model->isforbidden($data['user_id'],$data['act_id']);
		if (!empty($forbidden_status)) {
			if ($forbidden_status['user_id'] == $data['user_id'] && $forbidden_status['act_id'] == $data['act_id'] && $forbidden_status['status'] == 0) {
				//return Common_Util::returnJson('10004','该用户被屏蔽，不能发送弹幕');
				$data['check_status'] = 2;//审核不通过
			}
		}

		//根据发送弹幕人的用户id，获取用户信息
		$sender_user_info = $user_model->getUserInfo($data['user_id']);
		//print_r($sender_user_info);exit;
		//判断用户是否显示昵称
		if ($manager['show_nickname'] === 0) {
			$barrage_content_toPC = $data['content'];
		} else {
			$barrage_content_toPC = urlencode($sender_user_info[0]['nickname'].'：'.$data['content']); 
		}
		
		//1. 判断用户是否为管理员，为管理员的话直接修改其发送的弹幕状态为审核通过 1
		//2. 并且调用发送到PC端接口，将弹幕内容发送到PC端
		$is_not_play = true;//设置一个变量，如果用户是管理员且活动未开启审核，则将其设为false，避免重复向客户端发送弹幕
		if ($manager['user_id'] == $data['user_id']) {
			$is_not_play = false;
			$data['check_status'] = 1;//审核通过
			//将弹幕内容发送到PC端
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$manager['roomnum'].'&message=DANMAKU%3A'.$barrage_content_toPC;
			$html = Common_Util::RequestHttpArray('get',$url);
		}
		

		//1.判断活动是否开启审核(0开启 1不开启)，如果没有开启审核，新增弹幕状态设为审核通过
		//2.将弹幕内容发送到PC端
		//$result = $this->_model->isCheckable($data['act_id']);
		if ($manager['bar_check'] == 1 && $is_not_play) {//is_not_play变量用来记录管理员弹幕是否已经上屏幕，如果已经发送其值为false
			$data['check_status'] = 1;//审核通过
			//2.将弹幕内容发送到PC端
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$manager['roomnum'].'&message=DANMAKU%3A'.$barrage_content_toPC;
			$html = Common_Util::RequestHttpArray('get',$url);
		}

		$result = $this->_model->addBarrageContent($data);			
		if (is_numeric($result) && (0 < $result)) {
			return Common_Util::returnJson('20004','添加弹幕成功');
		}		
		
	}


	/**
	 * 添加高级弹幕
	 **/
	public function addSharpBarrageContentAction() {
		
		//弹幕参数处理
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n',''); 		//活动id
		$data['user_id'] = Common_Util::getHttpReqQuery($this,'user_id','Int','n',''); 		//用户id
		$data['content'] = Common_Util::getHttpReqQuery($this,'content','Str','n',''); 		//弹幕内容
		//$data['font_family'] = Common_Util::getHttpReqQuery($this,'font_family','Str','n',''); 	//弹幕颜色
		$data['font_color'] = Common_Util::getHttpReqQuery($this,'font_color','Str','n',''); 	//弹幕颜色
		//$data['font_size'] = Common_Util::getHttpReqQuery($this,'font_size','Int','n',''); 	//弹幕字体大小
		$data['monopoly'] = Common_Util::getHttpReqQuery($this,'monopoly','Int','n');//是否为霸屏弹幕
		$data['play_status'] = Common_Util::getHttpReqQuery($this,'play_status','Int','n'); //播放状态

		
		//判断该用户是否被禁用,如果禁用的话,修改其发布弹幕的状态为审核不通过 2
		$user_model = new UserModel();
		$forbidden_status = $user_model->isforbidden($data['user_id'],$data['act_id']);
		if (!empty($forbidden_status)) {
			if ($forbidden_status['user_id'] == $data['user_id'] && $forbidden_status['act_id'] == $data['act_id'] && $forbidden_status['status'] == 0) {
				//return Common_Util::returnJson('10004','该用户被屏蔽，不能发送弹幕');
				$data['check_status'] = 2;//审核不通过
			}
		}

		//根据发送弹幕人的用户id，获取用户信息
		$sender_user_info = $user_model->getUserInfo($data['user_id']);
		//print_r($sender_user_info);exit;
		//$barrage_content_toPC = urlencode('【'.$sender_user_info[0]['nickname'].'】：'.$data['content']); 
		
	
		$param = [
			'nickname' => $sender_user_info[0]['nickname'],
			'monopoly'=>$data['monopoly'],
			'play_status' => $data['play_status'],
			'content' => $data['content'],
			'font_color' => $data['font_color'],
			'create_time' => time()
		];
		//1. 判断用户是否为管理员，为管理员的话直接修改其发送的弹幕状态为审核通过 1
		//2. 并且调用发送到PC端接口，将弹幕内容发送到PC端
        $activity_model = new ActivityModel();
        $manager = $activity_model->getActManagerById($data['act_id']);
		$is_not_play = true;//设置一个变量，如果用户是管理员且活动未开启审核，则将其设为false，避免重复向客户端发送弹幕
		if ($manager['user_id'] == $data['user_id']) {
			$is_not_play = false;
			$data['check_status'] = 1;//审核通过
			//将弹幕内容发送到PC端
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$manager['roomnum'].'&message=SHARPDANMAKU%3A'.json_encode($param,JSON_UNESCAPED_UNICODE);
			$html = Common_Util::RequestHttpArray('get',$url);
		}
		//exit();

		//1.判断活动是否开启审核(0开启 1不开启)，如果没有开启审核，新增弹幕状态设为审核通过
		//2.将弹幕内容发送到PC端
		//$result = $this->_model->isCheckable($data['act_id']);
		if ($manager['bar_check'] == 1 && $is_not_play) {//is_not_play变量用来记录管理员弹幕是否已经上屏幕，如果已经发送其值为false
			$data['check_status'] = 1;//审核通过
			//2.将弹幕内容发送到PC端
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$manager['roomnum'].'&message=SHARPDANMAKU%3A'.json_encode($param,JSON_UNESCAPED_UNICODE);
			$html = Common_Util::RequestHttpArray('get',$url);
		}

		exit();
		$result = $this->_model->addSharpBarrageContent($data);			
		if ($result) {
			return Common_Util::returnJson('20004','添加高级弹幕成功');
		} else {
			return Common_Util::returnJson('20005','添加高级弹幕失败');
		}		
		
	}

	/**
	 * 测试霸屏
	 */
	public function testmonopolyAction() {
		
		$result = $this->_model->testapg();
		//print_r($result);exit;
		$url = "127.0.0.1:4237/?cmd=send_to_group&group_id=13824&message=SHARPDANMAKU%3A我是多排文字%0a的高级弹幕".$result['content'];
		echo $html = Common_Util::RequestHttpArray('get',$url);

	}

	/**
	 * 我参与的弹幕
	 */
	public function getMyParticipateAction() {

		//参数处理
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n','');	//用户ID
		$result = $this->_model->getMyParticipate($user_id);
		Common_Util::returnJson('20001','请求成功',$result);

	}

	/**
	 * 弹幕审核
	 */
	public function verifyBarrageContentAction() {

		//参数处理
		$data = [];
		$data['con_id'] = Common_Util::getHttpReqQuery($this,'con_id','Int','n',''); //弹幕ID
		$data['check_status'] = Common_Util::getHttpReqQuery($this,'check_status','Int','n',0);//弹幕审核结果	
	
		//查询出弹幕id 对应的弹幕信息，用于向客户端展示
		$review_content = $this->_model->getBarrageContentById($data['con_id']);
		if (empty($review_content)) {
			return Common_Util::returnJson('20001','无此弹幕信息');
		}
		$content = $review_content['content'];
		$roomnum = $review_content['roomnum'];
		$user_id = $review_content['user_id'];
		$act_id = $review_content['act_id'];
		if ($data['check_status'] != 1 && $data['check_status'] !=2) {
			return Common_Util::returnJson('10004','审核状态值错误');
		}

		//根据发送弹幕人的用户id，获取用户信息
		$user_model = new UserModel();
		$sender_user_info = $user_model->getUserInfo($user_id);
		//print_r($sender_user_info);exit;
		
		//判断是否显示用户昵称
		$activity_model = new ActivityModel();
		$manager = $activity_model->getActManagerById($act_id);
		if ($manager['show_nickname'] === 0) {
			$barrage_content_toPC = $content;
		} else {
			$barrage_content_toPC = urlencode($sender_user_info[0]['nickname'].'：'.$content); 
		}

		//审核弹幕
		$result = $this->_model->verifyBarrageContent($data);
		if ($result === false) {
			//$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum.'&message=DANMAKU:'.$content;
			//$html = Common_Util::RequestHttpArray('get',$url);
			//echo $html;
			return Common_Util::returnJson('20007','弹幕审核失败');
		}
		//如果弹幕审核状态值为1，表示审核通过，则将其发送到PC端
		if ($data['check_status'] == 1) {
			if ($review_content['play_status'] != 0 || $review_content['monopoly'] !=0) {
				//高级弹幕
				$param = [
					'nickname' => $sender_user_info[0]['nickname'],
					'monopoly'=>$review_content['monopoly'],
					'play_status' => $review_content['play_status'],
					'content' => $review_content['content'],
					'font_color' => $review_content['font_color']
				];
				$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum.'&message=SHARPDANMAKU%3A'.urlencode(json_encode($param,JSON_UNESCAPED_UNICODE));	
			} else {
				//普通弹幕
				$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum.'&message=DANMAKU%3A'.$barrage_content_toPC;
			}
			$html = Common_Util::RequestHttpArray('get',$url);
		}
		return Common_Util::returnJson('20006','弹幕审核完成');
		
	}
 
}
