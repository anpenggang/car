<?php

/**
 *
 * @name PayController
 * @desc 支付控制器 
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Pay.php v0.0 2017/12/4 新建
 */
class PayController extends BaseController {

	private $_model = null; //支付模型

	/**
	 * 初始化方法 控制器被调用的时候先调用初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		//实例化ContentModel
		$this->_model = new PayModel();

	}	

	/**
	 * 调用支付接口，高级弹幕进行支付 成功后数据直接进入数据库
	 */
	public function WxPayUnifiedOrderBarrageAction() {
		
		$total_fee = Common_Util::getHttpReqQuery($this,'total_fee','Int','n');
		$user_id = Common_Util::getHttpReqQuery($this,'user_id','Int','n');
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n');
		$content = Common_Util::getHttpReqQuery($this,'content','Str','n');
        
		// 判断活动是否开启，没开启的话不允许发送弹幕
        $activity_model = new ActivityModel();
        $manager = $activity_model->getActManagerById($act_id);
        if($manager['status'] != 1) {
            return Common_Util::returnJson('10004','活动未开启，不能发弹幕');
        }

        //判断屏幕是否在线，不在线的话直接返回，不让其发弹幕        
        $roomnum_data = $activity_model->getRoomnumById($act_id);//根据活动id获取房间编号
        $screen_status = $this->_redis->get($roomnum_data['roomnum']);//获取房间号对应的屏幕是否在线
        if ((int)$screen_status != 9) {
            return Common_Util::returnJson('10004','屏幕不在线，无法发弹幕');
        }

        //判断弹幕长度大于40字的返回错误
        if (mb_strlen($content,'utf-8') > 40){
            return Common_Util::returnJson('10001','弹幕长度大于40个字');
        }
		$user_model = new UserModel();
		$user_info = $user_model->getUserInfo($user_id);
		$openid = $user_info[0]['openid'];
		$out_trade_no = $this->generatePassword(3).time().$this->generatePassword(5);
		require_once APPLICATION_PATH.'/application/library/Pay/WXPaySDK/lib/WxPay.Api.php';
		//初始化值对象
    	$input = new WxPayUnifiedOrder();
		//参数规范：商家名称-销售商品类目
    	$input->SetBody("缤纷弹幕-高级弹幕");
		//订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
    	$trade_no = $input->SetOut_trade_no($out_trade_no);
		//费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
		$input->SetTotal_fee($total_fee);
		//$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
		$input->SetNotify_url("https://2.heibaixiaoyuan.com/Pay/notify");
		$input->SetTrade_type("JSAPI");
		//由小程序端传给服务端
    	$input->SetOpenid($openid);
		//向微信统一下单，并返回order，它是一个array数组
		$order = WxPayApi::unifiedOrder($input);
		$order['out_trade_no'] = $out_trade_no;
		//数据库记录
		$order_recorder = [
			'out_trade_no' => $out_trade_no,
			'total_fee' => $total_fee,
			'user_id' => $user_id,
			'act_id' => $act_id,
			'create_time' => time()
		];
		$this->_model->addPayRecorder($order_recorder);
		//json化返回给小程序端
    	header("Content-Type: application/json");
    	return Common_Util::returnJson('20001','请求成功',$order);

	}
	
	/*
	 * 弹幕支付成功之后的回调
	 */	
	public function notifyAction() {
		
		$key = 'Heibaixiaoyuanweixinzhifu1234567';
		$receipt = $_REQUEST;
		if ($receipt==null) {
			$receipt = file_get_contents("php://input");
			if ($receipt == null) {
				$receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
			}
		}
		$post_data = $this->xmlToArray($receipt); 
        $postSign = $post_data['sign'];
        unset($post_data['sign']);
        
        ksort($post_data);

        if (!empty($post_data['key'])) {
            $str = $this->toUrlParams($post_data);
        } else {
            $str = $this->toUrlParams($post_data).'&key='.$key; 
      	}
        $user_sign = strtoupper(md5($str)); 
      
		$order_msg = $this->_model->getPayRecordByBillNumber($post_data['out_trade_no']);
        if($post_data['return_code'] == 'SUCCESS' && $postSign == $user_sign && $order_msg['total_fee'] == $post_data['total_fee']){
            if ($order_msg['order_status'] == '1') {
                $this->returnSuccess();
            } else {
                $update_order_resulte = $this->_model->updateOrderStatus($post_data['out_trade_no']);
                if($update_order_resulte){
                    $this->returnSuccess();
                }
            }
        }else{
            echo '微信支付失败';
        }

	}
	
	/**
	 * 给微信发送确认订单金额和签名正确，SUCCESS信息
	 */
    private function returnSuccess(){

        $return['return_code'] = 'SUCCESS';
        $return['return_msg'] = 'OK';
        $xml_post = '<xml>
                    <return_code>'.$return['return_code'].'</return_code>
                    <return_msg>'.$return['return_msg'].'</return_msg>
                    </xml>';
        echo $xml_post;exit;

    }
	
	/**
	 * 微信回调时返回的XML
	 */
	public function testAction() {
		
		$str = "<xml>
					<appid><![CDATA[wx1fd9281f38e61901]]></appid>
					<bank_type><![CDATA[CFT]]></bank_type>
					<cash_fee><![CDATA[1]]></cash_fee>
					<fee_type><![CDATA[CNY]]></fee_type>
					<is_subscribe><![CDATA[N]]></is_subscribe>
					<mch_id><![CDATA[1336652401]]></mch_id>
					<nonce_str><![CDATA[0uli5bkauqa34hnhrjdyq3bw7kxoxrew]]></nonce_str>
					<openid><![CDATA[oTbPz0AiDWQN77CzI3pCzRUlfqAY]]></openid>
					<out_trade_no><![CDATA[9I31512440401s1TZj]]></out_trade_no>
					<result_code><![CDATA[SUCCESS]]></result_code>
					<return_code><![CDATA[SUCCESS]]></return_code>
					<sign><![CDATA[4D4E6FF6AE2127C52624EA2CE9876200]]></sign>
					<time_end><![CDATA[20171205102023]]></time_end>
					<total_fee>1</total_fee>
					<trade_type><![CDATA[JSAPI]]></trade_type>
					<transaction_id><![CDATA[4200000003201712059175370453]]></transaction_id>
				</xml>";
		return $str;
		echo $str;

	}

}
