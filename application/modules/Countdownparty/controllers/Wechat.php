<?php

class WechatController extends Yaf_Controller_Abstract {

	
	/**
	 * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
	 */
	public function init() {
		
		//本项目作为接口返回数据，关闭自动渲染视图
		Yaf_Dispatcher::getInstance()->disableView();

		//输出头消息，防止中文乱码
		header("Content-Type:text/html;charset=utf8");		

	}

	/**
	 * 微信端调用
	 */
	public function indexAction() {

		$wechat = new LaneWeChat\Core\Wechat(WECHAT_TOKEN,true);
		//echo $wechat->checkSignature();//第一次设置服务器配置时做url验证
		//$this->menulistAction();	
		//LaneWeChat\Core\Menu::delMenu();
		$this->menuListAction();
		//$this->broadcastAction();
		$apg = $wechat->run();
		echo $apg;
		//error_log(print_r($apg,true));
		exit();

	}

	/**
	 * 菜单列表
	 */
	public function menuListAction () {

		$menuList = array(
			array('id'=>'1', 'pid'=>'0', 'name'=>'预约通道', 'type'=>'', 'code'=>''),
			array('id'=>'2', 'pid'=>'0', 'name'=>'弹幕墙', 'type'=>'click', 'code'=>'danmuqiang'),
			//array('id'=>'3', 'pid'=>'0', 'name'=>'跨年夜', 'type'=>'', 'code'=>''),
			//array('id'=>'4', 'pid'=>'3', 'name'=>'活动介绍', 'type'=>'view', 'code'=>'http://mp.weixin.qq.com/s/-sTjBkae9KWXEcWawxOOTw'),
			//array('id'=>'5', 'pid'=>'3', 'name'=>'活动地点', 'type'=>'view', 'code'=>'http://mp.weixin.qq.com/s/qcH9NzQ46zXuwcHkaZCnKw'),	
			array('id'=>'6', 'pid'=>'1', 'name'=>'点击预约', 'type'=>'click', 'code'=>'dianjiyuyue'),
			array('id'=>'7', 'pid'=>'1', 'name'=>'领取福利', 'type'=>'click', 'code'=>'lingqufuli'),
			array('id'=>'8', 'pid'=>'1', 'name'=>'社团通道', 'type'=>'click', 'code'=>'shetuantongdao'),
			array('id'=>'9', 'pid'=>'1', 'name'=>'线上跨年', 'type'=>'click', 'code'=>'xianshangkuanian'),
			//array('id'=>'10', 'pid'=>'3', 'name'=>'弹幕墙', 'type'=>'click', 'code'=>'danmuqiang'),
			//array('id'=>'2', 'pid'=>'0', 'name'=>'菜单2', 'type'=>'', 'code'=>''),
			//array('id'=>'3', 'pid'=>'0', 'name'=>'地理位置', 'type'=>'location_select', 'code'=>'key_7'),
			//array('id'=>'4', 'pid'=>'1', 'name'=>'点击推事件', 'type'=>'click', 'code'=>'key_1'),
			//array('id'=>'5', 'pid'=>'1', 'name'=>'跳转URL', 'type'=>'view', 'code'=>'http://www.baidu.com'),
			//array('id'=>'6', 'pid'=>'2', 'name'=>'扫码推事件', 'type'=>'scancode_push', 'code'=>'key_2'),
			//array('id'=>'7', 'pid'=>'2', 'name'=>'扫码等收消息', 'type'=>'scancode_waitmsg', 'code'=>'key_3'),
			//array('id'=>'8', 'pid'=>'2', 'name'=>'系统拍照发图', 'type'=>'pic_sysphoto', 'code'=>'key_4'),
			//array('id'=>'9', 'pid'=>'2', 'name'=>'弹拍照或相册', 'type'=>'pic_photo_or_album', 'code'=>'key_5'),
			//array('id'=>'10', 'pid'=>'2', 'name'=>'弹微信相册', 'type'=>'pic_weixin', 'code'=>'key_6'),
		);
		$result = LaneWeChat\Core\Menu::setMenu($menuList);
		//error_log(print_r($result,true));		

	}

	public function updateAction() {
	
		$result = LaneWeChat\Core\Media::upload('https://2.heibaixiaoyuan.com/countdownparty/Danmaku/generatingQrcode', 'image');
		return $result;

	}

	public function broadcastAction() {

		$content = '记得来参加活动鸥';
		$toUserList = ['ojwi6wuHrlUG95FLRjuESJSLJ5aI','ojwi6wiKpkogZhXImuKdNPbgpVGc'];
		$result = LaneWeChat\Core\AdvancedBroadcast::sentTextByOpenId($toUserList, $content);
		error_log(print_r($result,true));

	}



}
