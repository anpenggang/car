<?php
namespace LaneWeChat\Core;
use Guzzle\Http\Message\Response;
/**
 * 处理请求
 * Created by Lane.
 * User: lane
 * Date: 13-12-19
 * Time: 下午11:04
 * Mail: lixuan868686@163.com
 * Website: http://www.lanecn.com
 */

class WechatRequest{
	/**
	 * @descrpition 分发请求
	 * @param $request
	 * @return array|string
	 */
	public static function switchType(&$request){
		$data = array();
//error_log(print_r($request,true));
		switch ($request['msgtype']) {
			//事件
		case 'event':
			$request['event'] = strtolower($request['event']);
			switch ($request['event']) {
				//关注
			case 'subscribe':
				//二维码关注
				if(isset($request['eventkey']) && isset($request['ticket'])){
					$data = self::eventQrsceneSubscribe($request);
					//普通关注
				}else{
					$data = self::eventSubscribe($request);
				}
				break;
				//扫描二维码
			case 'scan':
				$data = self::eventScan($request);
				break;
				//地理位置
			case 'location':
				$data = self::eventLocation($request);
				break;
				//自定义菜单 - 点击菜单拉取消息时的事件推送
			case 'click':
				$data = self::eventClick($request);
				break;
				//自定义菜单 - 点击菜单跳转链接时的事件推送
			case 'view':
				$data = self::eventView($request);
				break;
				//自定义菜单 - 扫码推事件的事件推送
			case 'scancode_push':
				$data = self::eventScancodePush($request);
				break;
				//自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
			case 'scancode_waitmsg':
				$data = self::eventScancodeWaitMsg($request);
				break;
				//自定义菜单 - 弹出系统拍照发图的事件推送
			case 'pic_sysphoto':
				$data = self::eventPicSysPhoto($request);
				break;
				//自定义菜单 - 弹出拍照或者相册发图的事件推送
			case 'pic_photo_or_album':
				$data = self::eventPicPhotoOrAlbum($request);
				break;
				//自定义菜单 - 弹出微信相册发图器的事件推送
			case 'pic_weixin':
				$data = self::eventPicWeixin($request);
				break;
				//自定义菜单 - 弹出地理位置选择器的事件推送
			case 'location_select':
				$data = self::eventLocationSelect($request);
				break;
				//取消关注
			case 'unsubscribe':
				$data = self::eventUnsubscribe($request);
				break;
				//群发接口完成后推送的结果
			case 'masssendjobfinish':
				$data = self::eventMassSendJobFinish($request);
				break;
				//模板消息完成后推送的结果
			case 'templatesendjobfinish':
				$data = self::eventTemplateSendJobFinish($request);
				break;
			default:
				return Msg::returnErrMsg(MsgConstant::ERROR_UNKNOW_TYPE, '收到了未知类型的消息', $request);
				break;
			}
			break;
			//文本
		case 'text':
			$data = self::text($request);
			break;
			//图像
		case 'image':
			$data = self::image($request);
			break;
			//语音
		case 'voice':
			$data = self::voice($request);
			break;
			//视频
		case 'video':
			$data = self::video($request);
			break;
			//小视频
		case 'shortvideo':
			$data = self::shortvideo($request);
			break;
			//位置
		case 'location':
			$data = self::location($request);
			break;
			//链接
		case 'link':
			$data = self::link($request);
			break;
		default:
			return ResponsePassive::text($request['fromusername'], $request['tousername'], '收到未知的消息，我不知道怎么处理');
			break;
		}
		return $data;
	}


	/**
	 * @descrpition 文本
	 * @param $request
	 * @return array
	 */
	public static function text(&$request){
		
		switch($request['content']) {
			case '跨年':
				$content = "2018跨年夜，让百万心愿飞一会儿”百万大学生跨年许愿活动正式开始啦！\n此次活动是由校园电影经纪计划发起，旨在为全国的大学生打造一个专属于你们的跨年盛宴～跨年活动将于2017年12月31日举办，分为线上和线下两种参与方式！\n想及时了解活动动态，请持续关注我们的官方微信公众号！各种信息，各种福利，都是你们的！\n有什么疑问请尽管私信公众号后台，小哥哥小姐姐都会给你一一解答！\n";
				$items[] = ResponseInitiative::newsItem("这个跨年夜，100万名95后要一起搞事情！", "一起狂欢才算跨年，自己一个人顶多算熬夜", "https://2.heibaixiaoyuan.com/public/images/20171221cdp2.jpg", "https://mp.weixin.qq.com/s/CAjVnBbWAJjJHYbDol9Byw");
       			ResponseInitiative::news($request['fromusername'],$items);
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '弹幕':
				$content = "跨年夜，我们将通过小程序及H5链接，实时参与弹幕互动。\n你的弹幕，将第一次实时展示在数十块电影银幕，十余块城市广场大屏，以及实时同步在百万大学生的手机小屏幕。届时，将第一次有100万个大学生与你一起同步发布弹幕，突破记录。\n指定活动参与链接，敬请关注本公众号后续消息推送，2017年12月31日，记得来参与哦！\n";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '社团':
				$content = "如果你是一社之长，或学生组织的负责人。欢迎你来加入我们的跨年活动！\n\n请你添加QQ好友2580549664\n或预先填写报名链接https://www.wjx.top/jq/18939728.aspx\n等待工作人员与你联系。\n通过资质审核后，你将加入本次活动的组织者联盟。\n不仅可以为你的小伙伴开启直达通道，本人还将获得一定的特权及荣誉！更重要的是，你还将获得由黑白校园and大地电影联名数家国内外知名企业 共同颁布的署名HBT参与证书。等你来参加哦！\n";
				$items[] = ResponseInitiative::newsItem("【社团通道】让你的爱豆，代你向母校说\"新年快乐\"！", "邀请明星大咖给母校送祝福？你的社团就可以哟！", "https://2.heibaixiaoyuan.com/public/images/20171221cdp3.jpg", "http://mp.weixin.qq.com/s/xaTwIxXyWdRFJDviXjpnQg");
				ResponseInitiative::news($request['fromusername'],$items);
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '电影':
				$content = "校园电影经纪计划\n\n校园电影经纪计划是由大地电影联合黑白校园共同发起，旨在挖掘有潜质的大学生电影人才（导演、制片、编剧、表演、宣发），为电影行业输送新鲜血液，一年一届的校园电影人才选拔活动。通过该活动最终筛选出来的优秀大学生电影人才将在大地电影的扶持下拍摄中国首部大学生“自编、自导、自演、自制片、自发行”的大学生电影。\n";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '抢票':
				$content = "点击购买链接→→\nhttp://api.stuhui.com/openapi/hbcard/weixin?act=FilmTicket&channel=123&from=singlemessage&isappinstalled=0\n";
				$items[] = ResponseInitiative::newsItem("2017年跨年夜，你可以约我向你表白吗？", "2017年又要过去啦", "https://2.heibaixiaoyuan.com/public/images/20171221cdp.jpg", "https://mp.weixin.qq.com/s/eY0MYMVBQJuMuA9-o_CBvg");
				ResponseInitiative::news($request['fromusername'],$items);
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '预约':
        		//@todo 预约逻辑处理
        		$user_model = new \CdpOAUserModel();
        		$user_info = $user_model->getUserInfoByOpenid($request['fromusername']);
        		//存在用户信息
        		if (!empty($user_info)) {
            		//更新用户关注状态 1 为关注
					if ($user_info['subscribe'] == 0) {//如果数据表中已经有记录，且用户状态为未订阅，则更新状态为订阅
            			$user_model->updateSubscribe($request['fromusername'],1);
					}
        		} else {
            		//新增用户信息
            		$wxuser = UserManage::getWxUserInfo($request['fromusername']);
            		if (!array_key_exists("errcode", $wxuser)) {
                		$addret = $user_model->addUser($wxuser);
            		} else {
                		//记录token错误获取情况原因
                		//error_log("wechatCallBack".print_r($wxuser,true));
                		//$content = "系统异常,报告开发者,有奖励哦".$request['fromusername'];
            		}   
        		}

				$content = "点击右侧链接：http://cn.mikecrm.com/uh35kbx 完成预约后记得保存截图哟！\n\n请不要取关本公众号，否则活动开始时，将无法收到活动参与链接哟！\n";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '小程序':
				return ResponsePassive::image($request['fromusername'], $request['tousername'], 'jOzZ1l1eZLQDZZb58CmwnMxG1Qak14dt1qFSaiLMI8E');
				break;
			case '任务奖励':
				$content = "呀！又有一个人完成了我们的秘密任务~嘘……本奖励只有参与过秘密任务的小伙伴才能获得哦！【回复你所在的城市】，按照对应提示提交你的收奖信息吧！";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '重庆':
				$content = "嘘……本奖励只有参与过秘密任务的重庆地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '济南':
				$content = "嘘……本奖励只有参与过秘密任务的济南地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '太原':
				$content = "嘘……本奖励只有参与过秘密任务的太原地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '芜湖':
				$content = "嘘……本奖励只有参与过秘密任务的芜湖地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '天津':
				$content = "嘘……本奖励只有参与过秘密任务的天津地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '合肥':
				$content = "嘘……本奖励只有参与过秘密任务的合肥地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case '群发apg':
				$content = 'apg嘘……本奖励只有参与过秘密任务的合肥地区小伙伴才能获得哦！你懂得~\n已完成任务并准备好截图的同学，快来加客服QQ:3391016587填写你的收奖信息吧！→_→';
        		$toUserList = ['ojwi6wuHrlUG95FLRjuESJSLJ5aI','ojwi6wiKpkogZhXImuKdNPbgpVGc'];
        		//$result = \LaneWeChat\Core\AdvancedBroadcast::sentImageByOpenId($toUserList, 'jOzZ1l1eZLQDZZb58CmwnMxG1Qak14dt1qFSaiLMI8E');//发送图片
				//$resilt = AdvancedBroadcast::sentNewsByOpenId($toUserList, $mediaId);//发送图文
				$result = AdvancedBroadcast::sentTextByOpenId($toUserList, $content);//发送文本
        		error_log(print_r($result,true));	

				//$accessToken = AccessToken::getAccessToken();
				//$url = 'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$accessToken;
				//$data = [
    			//	"type"=>"image",
    			//	"offset"=>0,
    			//	"count"=>1
				//	];
				//$data = json_encode($data);
				//$ret = \Common_Util::RequestHttpJson($url, $data);
				return;
				break;
			case '实时弹幕测试':

        		//@todo 预约逻辑处理
        		$user_model = new \CdpOAUserModel();
        		$user_info = $user_model->getUserInfoByOpenid($request['fromusername']);
        		//存在用户信息
        		if (!empty($user_info)) {
					$user_id = $user_info['id'];
            		//更新用户关注状态 1 为关注
					if ($user_info['subscribe'] == 0) {//如果数据表中已经有记录，且用户状态为未订阅，则更新状态为订阅
            			$user_model->updateSubscribe($request['fromusername'],1);
					}
        		} else {
            		//新增用户信息
            		$wxuser = UserManage::getWxUserInfo($request['fromusername']);
            		if (!array_key_exists("errcode", $wxuser)) {
                		$addret = $user_model->addUser($wxuser);
            		} else {
                		//记录token错误获取情况原因
                		//error_log("wechatCallBack".print_r($wxuser,true));
                		//$content = "系统异常,报告开发者,有奖励哦".$request['fromusername'];
            		}   
        		}

				$content = "点击右侧链接：http://2.heibaixiaoyuan.com/tmpcountdownparty/Index/index?user_id={$user_id} 观看弹幕";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);

				break;
			default: 

				break;	
		}	
		
	}

	/**
	 * @descrpition 图像
	 * @param $request
	 * @return array
	 */
	public static function image(&$request){
		//$content = '收到图片';
		$content = "收到！快来领取你的奖励吧！点右侧领取通用奖励：\n“欢喜首映APP”30天会员领取链接：\nhttps://www.huanxi.com/h5/active/index.html?code=BYBY；\n拉勾99元《面霸》课兑换券领取链接：\nhttps://wx20c9876c0cecee4d.h5.xiaoe-tech.com/coupon/get/cou_5a38cb211889b-t7y3Bu\n如果你是前200名预约报名的同学，24小时内小编会手动将优酷视频会员兑换码，发送给你哟！\n\n";
		//$wechatMsg=new \WechatMessageModel();
		//$msginfo=$wechatMsg->getMsg('cj_cyt');

		if (empty($msginfo))
		{
			//$content=str_replace("\r", "", $msginfo["msg"]);
		}
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 语音
	 * @param $request
	 * @return array
	 */
	public static function voice(&$request){
		if(!isset($request['recognition'])){
			$content = '收到语音';
			return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
		}else{
			//             $content = '收到语音识别消息，语音识别结果为：'.$request['recognition'];
			$content 	= "收到语音识别消息";
			return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
		}
	}

	/**
	 * @descrpition 视频
	 * @param $request
	 * @return array
	 */
	public static function video(&$request){
		$content = '收到视频';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 视频
	 * @param $request
	 * @return array
	 */
	public static function shortvideo(&$request){
		$content = '收到小视频';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 地理
	 * @param $request
	 * @return array
	 */
	public static function location(&$request){
		$content = '收到上报的地理位置';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 链接
	 * @param $request
	 * @return array
	 */
	public static function link(&$request){
		$content = '收到连接';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 关注
	 * @param $request
	 * @return array
	 */
	public static function eventSubscribe(&$request){
		$content = 'Hello~ 欢迎关注校园电影经纪计划。 

校园电影经纪计划是由大地电影联合黑白校园共同发起，旨在挖掘有潜质的大学生电影人才（导演、制片、编剧、表演、宣发），为电影行业输送新鲜血液，一年一届的校园电影人才选拔活动。

2017年12月31日，我们将举行校园电影经纪计划启动仪式－－“2018跨年夜，让百万心愿飞一会儿”百万大学生跨年许愿活动。

回复【跨年】，了解跨年许愿活动的活动详情。

回复【抢票】，抢购跨年夜活动线下入场门票。

回复【弹幕】，了解如何在跨年夜发布弹幕。

回复【社团】，欢迎社团、学生组织这加入！

回复【电影】，了解校园电影经纪计划详情。

回复【任务奖励】，只有做过任务的同学才能领取的奖励哟！

回复【预约】，完成线上弹幕跨年活动预约。预约完成后可凭截图领取奖励哟！

期待与你一起，完成95后迈上历史舞台的第一次惊艳亮相 
';
		//@todo 普通关注 新增用户信息 回复默认用户信息
		$user_model = new \CdpOAUserModel();

		$user_info = $user_model->getUserInfoByOpenid($request['fromusername']);

		//存在用户信息
		if (!empty($user_info)) {
				//error_log(print_r($user_info,true));
			//更新用户关注状态 1 为关注
			$user_model->updateSubscribe($request['fromusername'],1);
		}else {
			//新增用户信息
			$wxuser = UserManage::getWxUserInfo($request['fromusername']);
			//error_log(print_r($wxuser,true));
			if (!array_key_exists("errcode", $wxuser)) {
				$addret = $user_model->addUser($wxuser);
				//error_log(print_r($wxuser,true));

			} else {
				//记录token错误获取情况原因
				//error_log("wechatCallBack".print_r($wxuser,true));
				//         		$content = "系统异常,报告开发者,有奖励哦".$request['fromusername'];
			}	
		}

		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 取消关注
	 * @param $request
	 * @return array
	 */
	public static function eventUnsubscribe(&$request){
		$content = '为什么不理我了？';

		$user_model = new \CdpOAUserModel();

		//$userinfo = $wechatUser->getUserinfo($request['fromusername']);

		//来源二维码用户
		//if ("admin" != $userinfo["scene_str"])
		//{
		//	$qrscene 		= substr($userinfo['scene_str'], 8);
		//	if ($qrscene > 100000)
		//	{
		//		$invister_userinfo 	= $wechatUser->where(['id'=>($qrscene-100000)])->getOne("wx_hbcard_userinfo");
		//	}
		//}

		//数据库更新用户关注状态 0 用户取消关注
		$user_model->updateSubscribe($request['fromusername'],0);

		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 扫描二维码关注（未关注时）
	 * @param $request
	 * @return array
	 */
	public static function eventQrsceneSubscribe(&$request){

	    /*
        *用户扫描带参数二维码进行自动分组
        *此处添加此代码是大多数需求是在扫描完带参数二维码之后对用户自动分组
        */
        $sceneid = str_replace("qrscene_","",$request['eventkey']);
        //移动用户到相应分组中去,此处的$sceneid依赖于之前创建时带的参数
        if(!empty($sceneid)){
            UserManage::editUserGroup($request['fromusername'], $sceneid);
            $result=UserManage::getGroupByOpenId($request['fromusername']);
            //方便开发人员调试时查看参数正确性
            $content = '欢迎您关注我们的微信，将为您竭诚服务。二维码Id:'.$result['groupid'];
        }else
            $content = '欢迎您关注我们的微信，将为您竭诚服务。';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 扫描二维码（已关注时）
	 * @param $request
	 * @return array
	 */
	public static function eventScan(&$request){
		$content = '您已经关注了哦～';
        return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 上报地理位置
	 * @param $request
	 * @return array
	 */
	public static function eventLocation(&$request){
		$content = '收到上报的地理位置';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 点击菜单拉取消息时的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventClick(&$request){
		
		//获取该分类的信息
		//error_log(print_r($request,true));
        $eventKey = $request['eventkey'];
		switch ($eventKey) {
			case 'zhuanfafuli':
				$content = "请回复您所参与活动的城市代号，获取指定获奖链接。";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case 'yuyuetongdao':
				$content = "点击右侧链接：http://cn.mikecrm.com/uh35kbx完成预约后记得保存截图，在此界面发送截图可领取奖励哟！\n\n请务必关注本公众号，否则活动开始时，将无法收到活动参与链接哟！\n";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case 'dianjiyuyue':
				$content = "点击右侧链接：http://cn.mikecrm.com/uh35kbx 完成预约后记得保存截图哟！\n\n请不要取关本公众号，否则活动开始时，将无法收到活动参与链接哟！\n";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case 'lingqufuli':
				$content = "发送刚刚预约成功的界面截图，至本界面。收到截图后，小编会把福利发送给你哦！";
				return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;	
			case 'shetuantongdao':	
				$items[] = ResponseInitiative::newsItem("【社团通道】让你的爱豆，代你向母校说\"新年快乐\"！", "邀请明星大咖给母校送祝福？你的社团就可以哟！", "https://2.heibaixiaoyuan.com/public/images/20171221cdp3.jpg", "http://mp.weixin.qq.com/s/xaTwIxXyWdRFJDviXjpnQg");
				ResponseInitiative::news($request['fromusername'],$items);
				return;
				//$content = "点击链接，查看详情！\nhttp://mp.weixin.qq.com/s/xaTwIxXyWdRFJDviXjpnQg";
				//return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case 'xianshangkuanian':	
				//error_log(print_r($request,true));
				$items[] = ResponseInitiative::newsItem("已报名的1032356名同学，你有一个跨年狂欢夜需要确认收货！", "快速预约通道，直通2018年线上跨年狂欢弹幕墙。", "https://2.heibaixiaoyuan.com/public/images/20171221cdp4.jpg", "http://mp.weixin.qq.com/s/Yt-mxN-niHJfTD33zIQj6Q");
				ResponseInitiative::news($request['fromusername'],$items);
				return;
				//$content = "点击链接，查看详情！\nhttp://mp.weixin.qq.com/s/Yt-mxN-niHJfTD33zIQj6Q";
				//return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
				break;
			case 'danmuqiang':
				return ResponsePassive::image($request['fromusername'], $request['tousername'], 'jOzZ1l1eZLQDZZb58CmwnMxG1Qak14dt1qFSaiLMI8E');
				break;
			default:
				break;	
				
			
		}

	}

	/**
	 * @descrpition 自定义菜单 - 点击菜单跳转链接时的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventView(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到跳转链接事件，您设置的key是' . $eventKey;
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 扫码推事件的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventScancodePush(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到扫码推事件的事件，您设置的key是' . $eventKey;
		$content .= '。扫描信息：'.$request['scancodeinfo'];
		$content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
		$content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 扫码推事件且弹出“消息接收中”提示框的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventScancodeWaitMsg(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到扫码推事件且弹出“消息接收中”提示框的事件，您设置的key是' . $eventKey;
		$content .= '。扫描信息：'.$request['scancodeinfo'];
		$content .= '。扫描类型(一般是qrcode)：'.$request['scantype'];
		$content .= '。扫描结果(二维码对应的字符串信息)：'.$request['scanresult'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 弹出系统拍照发图的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicSysPhoto(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到弹出系统拍照发图的事件，您设置的key是' . $eventKey;
		$content .= '。发送的图片信息：'.$request['sendpicsinfo'];
		$content .= '。发送的图片数量：'.$request['count'];
		$content .= '。图片列表：'.$request['piclist'];
		$content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 弹出拍照或者相册发图的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicPhotoOrAlbum(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到弹出拍照或者相册发图的事件，您设置的key是' . $eventKey;
		$content .= '。发送的图片信息：'.$request['sendpicsinfo'];
		$content .= '。发送的图片数量：'.$request['count'];
		$content .= '。图片列表：'.$request['piclist'];
		$content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 弹出微信相册发图器的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventPicWeixin(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到弹出微信相册发图器的事件，您设置的key是' . $eventKey;
		$content .= '。发送的图片信息：'.$request['sendpicsinfo'];
		$content .= '。发送的图片数量：'.$request['count'];
		$content .= '。图片列表：'.$request['piclist'];
		$content .= '。图片的MD5值，开发者若需要，可用于验证接收到图片：'.$request['picmd5sum'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * @descrpition 自定义菜单 - 弹出地理位置选择器的事件推送
	 * @param $request
	 * @return array
	 */
	public static function eventLocationSelect(&$request){
		//获取该分类的信息
		$eventKey = $request['eventkey'];
		$content = '收到点击跳转事件，您设置的key是' . $eventKey;
		$content .= '。发送的位置信息：'.$request['sendlocationinfo'];
		$content .= '。X坐标信息：'.$request['location_x'];
		$content .= '。Y坐标信息：'.$request['location_y'];
		$content .= '。精度(可理解为精度或者比例尺、越精细的话 scale越高)：'.$request['scale'];
		$content .= '。地理位置的字符串信息：'.$request['label'];
		$content .= '。朋友圈POI的名字，可能为空：'.$request['poiname'];
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * 群发接口完成后推送的结果
	 *
	 * 本消息有公众号群发助手的微信号“mphelper”推送的消息
	 * @param $request
	 */
	public static function eventMassSendJobFinish(&$request){
		//发送状态，为“send success”或“send fail”或“err(num)”。但send success时，也有可能因用户拒收公众号的消息、系统错误等原因造成少量用户接收失败。err(num)是审核失败的具体原因，可能的情况如下：err(10001), //涉嫌广告 err(20001), //涉嫌政治 err(20004), //涉嫌社会 err(20002), //涉嫌色情 err(20006), //涉嫌违法犯罪 err(20008), //涉嫌欺诈 err(20013), //涉嫌版权 err(22000), //涉嫌互推(互相宣传) err(21000), //涉嫌其他
		$status = $request['status'];
		//计划发送的总粉丝数。group_id下粉丝数；或者openid_list中的粉丝数
		$totalCount = $request['totalcount'];
		//过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数，原则上，FilterCount = SentCount + ErrorCount
		$filterCount = $request['filtercount'];
		//发送成功的粉丝数
		$sentCount = $request['sentcount'];
		//发送失败的粉丝数
		$errorCount = $request['errorcount'];
		$content = '发送完成，状态是'.$status.'。计划发送总粉丝数为'.$totalCount.'。发送成功'.$sentCount.'人，发送失败'.$errorCount.'人。';
		return ResponsePassive::text($request['fromusername'], $request['tousername'], $content);
	}

	/**
	 * 群发接口完成后推送的结果
	 *
	 * 本消息有公众号群发助手的微信号“mphelper”推送的消息
	 * @param $request
	 */
	public static function eventTemplateSendJobFinish(&$request){
		//发送状态，成功success，用户拒收failed:user block，其他原因发送失败failed: system failed
		$status = $request['status'];
		if($status == 'success'){
			//发送成功
		}else if($status == 'failed:user block'){
			//因为用户拒收而发送失败
		}else if($status == 'failed: system failed'){
			//其他原因发送失败
		}
		return true;
	}


	public static function test(){
		// 第三方发送消息给公众平台
		$encodingAesKey = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFG";
		$token = "pamtest";
		$timeStamp = "1409304348";
		$nonce = "xxxxxx";
		$appId = "wxb11529c136998cb6";
		$text = "<xml><ToUserName><![CDATA[oia2Tj我是中文jewbmiOUlr6X-1crbLOvLw]]></ToUserName><FromUserName><![CDATA[gh_7f083739789a]]></FromUserName><CreateTime>1407743423</CreateTime><MsgType><![CDATA[video]]></MsgType><Video><MediaId><![CDATA[eYJ1MbwPRJtOvIEabaxHs7TX2D-HV71s79GUxqdUkjm6Gs2Ed1KF3ulAOA9H1xG0]]></MediaId><Title><![CDATA[testCallBackReplyVideo]]></Title><Description><![CDATA[testCallBackReplyVideo]]></Description></Video></xml>";


		$pc = new Aes\WXBizMsgCrypt($token, $encodingAesKey, $appId);
		$encryptMsg = '';
		$errCode = $pc->encryptMsg($text, $timeStamp, $nonce, $encryptMsg);
		if ($errCode == 0) {
			print("加密后: " . $encryptMsg . "\n");
		} else {
			print($errCode . "\n");
		}

		$xml_tree = new \DOMDocument();
		$xml_tree->loadXML($encryptMsg);
		$array_e = $xml_tree->getElementsByTagName('Encrypt');
		$array_s = $xml_tree->getElementsByTagName('MsgSignature');
		$encrypt = $array_e->item(0)->nodeValue;
		$msg_sign = $array_s->item(0)->nodeValue;

		$format = "<xml><ToUserName><![CDATA[toUser]]></ToUserName><Encrypt><![CDATA[%s]]></Encrypt></xml>";
		$from_xml = sprintf($format, $encrypt);

		// 第三方收到公众号平台发送的消息
		$msg = '';
		$errCode = $pc->decryptMsg($msg_sign, $timeStamp, $nonce, $from_xml, $msg);
		if ($errCode == 0) {
			print("解密后: " . $msg . "\n");
		} else {
			print($errCode . "\n");
		}
	}

}
