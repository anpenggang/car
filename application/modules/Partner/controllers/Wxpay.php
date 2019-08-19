<?php
class WxpayController extends BaseController {

    private $_model = null;
	private $mchid  = '1347559101';      					//微信支付商户号
	private $appid  = 'wx76997afa9db064aa';  				//微信支付申请对应的公众号的APPID
	private $apiKey = 'beijingaotezhikejiyouxiangongsi1';   //https://pay.weixin.qq.com 帐户设置-安全设置-API安全-API密钥-设置API密钥	
        
    /** 
     * 初始化方法
     */
    public function init() {

        //调用父类的初始化方法
        parent::init();

        //初始化用户模型
        $this->_model = new PtnWithdrawalsModel();

    }
	
	/**
	 *企业付款给指定用户
	 */
	public function WXPayAction(){
		$withdrawals_id = Common_Util::getHttpReqQuery($this,'withdrawals_id','Int','n');//提现申请表id
		$ret = $this->_model->getWithdrawalsInfo($withdrawals_id);
		if (empty($ret)) {
			return Common_Util::returnJson('20002','无对应数据');
		}
		//判断支付状态为管理员审核通过且未支付,则发起支付
		if ($ret['verify_status'] == 2 && $ret['status'] != 4) {

			$user_model = new PtnUserModel();
			$userinfo = $user_model->getUserInfo($ret['user_id']);
			$openId = $userinfo['openid'];//用户openid
			$payAmount = $ret['cash'];//提现金额
			$outTradeNo = uniqid();//随机串
			$trueName = $userinfo['realname'];//用户真实姓名
			$desc = '校园传媒小程序提现';//账单描述
			$result = $this->createJsBizPackage($openId,$payAmount,$outTradeNo,$trueName,$desc);

			if ($result['return_code'] == "SUCCESS" 
				&& $result['result_code'] == "SUCCESS" 
				&& $result['partner_trade_no'] == $outTradeNo) {
				//提现成功，提现表状态
				$update_data = [
					'status' => 4,
					'wx_reason' => '',
					'partner_trade_no' => $result['partner_trade_no'],
					'payment_no' => $result['payment_no'],
					'payment_time' => $result['payment_time']
				];
				$notify_res = $this->_model->wxNotify($withdrawals_id,$update_data);
				if (!$notify_res) {
					return Common_Util::returnJson('20006','提现操作失败，请重试');
				}
				return Common_Util::returnJson('20007','提现操作成功');
			} else {
				$update_data = [
					'status' => 3,
					'wx_reason' => json_encode($result,JSON_UNESCAPED_UNICODE),
				];
				$notify_res = $this->_model->wxNotify($withdrawals_id,$update_data);
				return Common_Util::returnJson('20006','操作失败');
			}
		} else {		
			$ret = $this->_model->getWithdrawalsInfo($withdrawals_id);
			if ($ret['verify_status'] == 2 && $ret['status'] == 4) {
				return Common_Util::returnJson('20007','订单已完成');
			} else {
				return Common_Util::returnJson('20006','订单有误，请重试');
			}
		}

	}

	/**
	 * 用户支付给企业
	 */
	public function payAction() {

		$serverip = $_SERVER['SERVER_ADDR'];
		$url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
		$config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		if ($config->run->env == 'debug') {
			$notify_url = "http://fe3.stuhui.com/Partner/Wxpay/notify";
		} else {
			$notify_url = "http://2.heibaixiaoyuan.com/Partner/Wxpay/notify";
		}
		$total_fee = '100';
		$openid = 'oZ6R_4v7ONY4ILdcYW_ZgQc5sAC4';
        $unified = array(
            'appid' => $this->appid,						//小程序appID
            'mch_id' => $this->mchid,						//商户好
            'nonce_str' => $this->randomStrNum(16),				//随机字符串，长度要求在32位以内。
			'out_trade_no' => $this->generatePassword(3).time().$this->generatePassword(5), //商户系统内部订单号
            'openid' => $openid,
			'notify_url' => $notify_url,
			'trade_type' => 'JSAPI',
			'body' => '校园代理人-商品购买',
			'total_fee' => $total_fee,
			'spbill_create_ip' => $serverip,
        );
        $unified['sign'] = $this->getSign($unified,$this->apiKey);
        $responseXml = $this->curlPost($url, $this->arrayToXml($unified));
		$response_arr = $this->xmlToArray($responseXml);
		if ($response_arr['return_code'] == "SUCCESS" && $response_arr['result_code'] == "SUCCESS") { 
            //数据库记录
            //$order_recorder = [ 
            //    'out_trade_no' => $out_trade_no,
            //    'total_fee' => $total_fee,
            //    'user_id' => $this->_userinfo['user_id'],
            //    'chief_id' => $chief_id,
            //    'pay_method' => $pay_method,
            //    'create_time' => time()
            //];  
            //$ret = $this->_model->addPayRecorder($order_recorder);
            //if ($ret) {
                //json化返回给小程序端
                header("Content-Type: application/json");
                return Common_Util::returnJson('20001','下单成功',$response_arr);
            //}   
        }   
        return Common_Util::returnJson('10004','下单有误，请重试');

	}
	
	/*  
     * 课程支付成功之后的回调
     */ 
    public function notifyAction() {
    
        $receipt = file_get_contents("php://input");
        if ($receipt == null) {
            $receipt = $GLOBALS['HTTP_RAW_POST_DATA'];
            if ($receipt == null) {
                $receipt = $_REQUEST;
            }   
        }   
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
     * 企业付款下单
     */
    private function createJsBizPackage($openid, $totalFee, $outTradeNo,$trueName,$desc) {
		$serverip = $_SERVER['SERVER_ADDR'];
        $config = array(
            'mch_id' => $this->mchid,
            'appid' => $this->appid,
            'key' => $this->apiKey,
        );
        $unified = array(
            'mch_appid' => $config['appid'],
            'mchid' => $config['mch_id'],
            'nonce_str' => $this->randomStrNum(16),
            'openid' => $openid,
            'check_name'=>'NO_CHECK',        //校验用户姓名选项。NO_CHECK：不校验真实姓名，FORCE_CHECK：强校验真实姓名
            're_user_name'=>$trueName,                 //收款用户真实姓名（不支持给非实名用户打款）
            'partner_trade_no' => $outTradeNo,
            'spbill_create_ip' => $serverip,
            'amount' => intval($totalFee * 100),       //单位 转为分
            'desc'=>$desc,            //企业付款操作说明信息
        );
        $unified['sign'] = $this->getSign($unified, $config['key']);
        $responseXml = $this->curlPost('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $this->arrayToXml($unified));
		$response_arr = $this->xmlToArray($responseXml);
		error_log(print_r($response_arr,true),3,'/home/wwwlogs/partnerBillDetails.log');
		return $response_arr;

    }	
	
	private function getSign($params, $key) {
        ksort($params, SORT_STRING);
        $unSignParaString = $this->formatQueryParaMap($params, false);
        $signStr = strtoupper(md5($unSignParaString . "&key=" . $key));
        return $signStr;
    }

    private function formatQueryParaMap($paraMap, $urlEncode = false) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if (null != $v && "null" != $v) {
                if ($urlEncode) {
                    $v = urlencode($v);
                }
                $buff .= $k . "=" . $v . "&";
            }
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

	private function curlPost($url = '', $postData = '', $options = array()) {
        if (is_array($postData)) {
            $postData = http_build_query($postData);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); //设置cURL允许执行的最长秒数
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //https请求 不验证证书和host
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //第一种方法，cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
         curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
         curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/backup/cert/apiclient_cert.pem');
        //默认格式为PEM，可以注释
         curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
         curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/backup/cert/apiclient_key.pem');
        //第二种方式，两个文件合成一个.pem文件
      // curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/all.pem');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
	
}//endclass	
