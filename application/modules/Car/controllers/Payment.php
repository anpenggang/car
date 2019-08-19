<?php

/**
 * @name PaymentController
 * @desc 支付控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Payment.php v0.0 2018/4/13 新建
 * @version				v1.0 2018/5/17 重写该方法
 */
class PaymentController extends BaseController {

	private $_model = null; 
	private $_userinfo = [];

	/**
	 *初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		
		//实例化支付类模型
		$this->_model = new LctPaymentModel();

	}
	
	/**
	 * 调用支付接口，进行课程购买
	 */
	public function WxPayUnifiedOrderAction() {

		//根据sessionID获取用户id
		$this->_userinfo = $this->verifySessionid($this);
		
		$chief_id = Common_Util::getHttpReqQuery($this,'chief_id','Int','n');//课程id
		$pay_method = Common_Util::getHttpReqQuery($this,'pay_method','Int','n');//支付方法
		$periods = Common_Util::getHttpReqQuery($this,'periods','Int','n');//课程id
        
		$chief_model = new LctChiefModel();
		$limit_num = $chief_model->getLimitNum($chief_id,$periods);
		if ($limit_num <= 0) {
			return Common_Util::returnJson('10004','课程已售罄');
		}
		//查询用户表获取openid
		$user_model = new LctUserModel();
		$user_info = $user_model->getUserInfo($this->_userinfo['user_id']);
		$openid = $user_info['openid'];

		//查询课程表，获取课程价格
		$chief_model = new LctChiefModel();
		$chief_info = $chief_model->getChiefInfoById($chief_id);
		if ($pay_method == 1) {//分享购买
			$total_fee = $chief_info['price_prime'] - $chief_info['share_discount'];
			$total_fee = round($total_fee,2);
		} else {
			$total_fee = $chief_info['price_prime'];
		}

		//error_log(print_r($total_fee,true));

		$out_trade_no = $this->generatePassword(3).time().$this->generatePassword(5);
		require_once APPLICATION_PATH.'/application/library/Pay/WXPaySDK/lib/WxPay.Api.php';
    	$input = new WxPayUnifiedOrder();
    	$input->SetBody("入行-课程购买");
    	$trade_no = $input->SetOut_trade_no($out_trade_no);
		$input->SetTotal_fee($total_fee*100);
		
		$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		if ($config->run->env == 'debug') {
			$input->SetNotify_url("http://fe3.stuhui.com/Lecture/Payment/notify");
		} else {
			$input->SetNotify_url("https://2.heibaixiaoyuan.com/Lecture/Payment/notify");
		}
		$input->SetTrade_type("JSAPI");
    	$input->SetOpenid($openid);
		//向微信统一下单，并返回order，它是一个array数组
		$order = WxPayApi::unifiedOrder($input);
		$order['out_trade_no'] = $out_trade_no;
		//error_log(print_r($input,true));
		//error_log(print_r($order, true));
		if ($order['return_code'] == "SUCCESS" && $order['result_code'] == "SUCCESS") { 
			//数据库记录
			$order_recorder = [
				'out_trade_no' => $out_trade_no,
				'total_fee' => $total_fee,
				'user_id' => $this->_userinfo['user_id'],
				'chief_id' => $chief_id,
				'pay_method' => $pay_method,
				'periods' => $periods,
				'create_time' => time()
			];
			$ret = $this->_model->addPayRecorder($order_recorder);
			if ($ret) {
    			return Common_Util::returnJson('20001','请求成功',$order);
			}
		}
		unset($order['appid']);
		unset($order['mch_id']);
		return Common_Util::returnJson('10004','下单有误，请重试',$order);

	}
	
	/*
	 * 课程支付成功之后的回调
	 */	
	public function notifyAction() {
		
		$key = 'Heibaixiaoyuanweixinzhifu1234567';
		$receipt = file_get_contents("php://input");
		if ($receipt == null) {
			$receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
			if ($receipt == null) {
				$receipt = $_REQUEST;
			}
		}
		//error_log(print_r($receipt,true));
		$post_data = $this->xmlToArray($receipt);   //微信支付成功，返回回调地址url的数据：XML转数组Array
		error_log(print_r($post_data, true), 3, '/home/wwwlogs/lecturePayDetails.log');
        $postSign = $post_data['sign'];
        unset($post_data['sign']);    //这里很重要哦，一定要将返回的sign剔除掉
        
        /* 微信官方提醒：
         *  商户系统对于支付结果通知的内容一定要做【签名验证】,
         *  并校验返回的【订单金额是否与商户侧的订单金额】一致，
         *  防止数据泄漏导致出现“假通知”，造成资金损失。
         */
        ksort($post_data);// 对数据进行排序
        //正常情况微信是不会返回支付的key的，为保万一我们判断一下
        if (!empty($post_data['key'])) {
            $str = $this->toUrlParams($post_data);//对数组数据拼接成key=value字符串
        } else {
            $str = $this->toUrlParams($post_data).'&key='.$key; //这里也一定要加上key，不然签名就错了
      	}
        $user_sign = strtoupper(md5($str));   //再次生成签名，与$postSign比较
      
		$order_msg = $this->_model->getPayRecordByBillNumber($post_data['out_trade_no']);
		//$order_msg = $this->_model->getPayRecordByBillNumber(1512440426);//测试
        /*
         *  分别判断返回状态码、返回签名sign、返回订单总金额，三者同时为真，订单交易成功，状态修改为1
        */
        if($post_data['return_code'] == 'SUCCESS' 
			&& $postSign == $user_sign 
			&& (($order_msg['total_fee'] * 100) == $post_data['total_fee']) 
		) {

            /*
            * 首先判断，订单是否已经更新为 9，因为微信会总共发送8次回调确认
            * 其次，订单已经为 9 的，直接返回SUCCESS
            * 最后，订单没有为 9 的，更新状态为 9，返回SUCCESS
            */
            if ($order_msg['order_status'] == '1') {
                $this->returnSuccess();
            } else {
                $update_order_resulte = $this->_model->updateOrderStatus($post_data);
                //$update_order_resulte = $this->_model->updateOrderStatus(1512440426);
                if($update_order_resulte){
                    $this->returnSuccess();
                }
            }
        }else{
            //echo '微信支付失败';
			error_log(print_r($receipt,true));
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
	
}//endclass
