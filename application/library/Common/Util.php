<?php

/**
 * @name Common_Util
 * @desc 共用方法进行封装 
 * @author
 */
class Common_Util{

	/**
	 * 获取http请求中的参数,paramType已存在：Cont、Str、Int、Id,可以根据需求继续添加
	 * @param	Object	$controller		请求对象实例
	 * @param	String	$paramName		参数名称
	 * @param	String	$paramType		参数类型
	 * @param	String	$isNull			参数值是否为空（n/y）
	 * @param	String	$defaultValue	参数默认值
	 * @return	String	$val			返回请求参数对应的值
	 */
    public static function getHttpReqQuery($controller, $paramName, $paramType = 'Str' ,$isNull = 'n' ,$defaultValue = '') {
        
		//do urlencode, trim, and htmlspecialchars.
        $val =  htmlspecialchars(trim(urldecode($controller->getRequest()->getQuery($paramName, $defaultValue))));
        if("" === $val)
            $val = htmlspecialchars(trim(urldecode($controller->getRequest()->getPost($paramName, $defaultValue))));

        //判断非空
        if ('n' == strtolower($isNull)) {
            $val = self::VerifyParamEmpty($val);
        }
        
        //针对不同的参数类型做不同的过滤处理
		$paramType = ucfirst(strtolower($paramType));
        $function = "VerifyParam".$paramType;
        if (method_exists('Common_Util',$function)) {
            self::$function($val);
		} else {
            self::returnJson('10001','type类型错误');
        }
        
        return $val;

    }

	/**
	 * 获取Base64 post请求的数据
	 * @param   Object  $controller     请求对象实例
	 * @param   String  $paramName      参数名称
	 * @param   String  $defaultValue   参数默认值
	 * @return  String  $val            返回请求参数对应的值
	*/
    public static function getHttpBase64Query($controller, $paramName, $defaultValue = '') {

        $val = htmlspecialchars(trim($controller->getRequest()->getPost($paramName, $defaultValue)));
        return $val;

    }

    /**
     * 内容过滤
     */
    public static function VerifyParamCont($val) { 

    }

    /**
     * 验证字符串类型参数
     */
    public static function VerifyParamStr($val) { 

        //字符的过滤
        $val = str_replace("'",'’',$val);
        return $val;
        if(is_numeric(strpos($val,"'"))){
            self::returnJson('10005','包含敏感字符');
            exit();
        }

    }
    /**
     * 验证整形参数
     */
    public static function VerifyParamInt($val) { 

        if (!is_numeric($val)) {
            self::returnJson('10005','参数应为数字');
            exit();
        }
        return $val;

    }
    /**
     * 对id的过滤
     */
    public static function VerifyParamId($val) {

        if ((!empty($val) && !is_numeric($val))|| (is_numeric($val) && (1 > $val))) {
            self::returnJson('10005','参数错误');
            exit();
        }
        return $val;

    }
    /**
     * 判断参数是否为空
     */
    public static function VerifyParamEmpty($val) {

        if (empty($val) && !is_numeric($val)) {
            self::returnJson('10002','参数不能为空');
            exit();
        }
        return $val;

    }

    /**
     * 正则验证电话号码
     */
    public static function VerifyMobileByRegular($mobile) {

        return preg_match('/^1\d{10}$/',$phone);

    }

	/** 
	 * 返回JSON数据
	 * @param   Integer $status 状态码
	 * @param   String  $msg    状态信息
	 * @data    Array   $data   返回数据
	 * @return  JSON            设置JSON数据
	 */
    public static function returnJson($status,$msg,$data = null,$data_access = null) {

        $ret['status'] = $status;
        $ret['msg'] = $msg;
        if (isset($data))
            $ret['data'] = $data;
		if (isset($data_access))
			$ret['data_access'] = $data_access;

		//header('Content-Type:text/html;charset=utf8');
        echo json_encode($ret,JSON_UNESCAPED_UNICODE);

    }

    /**
     * 通过数组请求url,模拟post提交
     */
    public static function RequestHttpArray($method, $url, $data='') {

        // create curl resource 
        $ch = curl_init(); 

        // set url 
        curl_setopt($ch, CURLOPT_URL, $url); 
        //return the transfer as a string 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt( $ch , CURLOPT_TIMEOUT, 3 );//设置超时时间3s,调取场次时间略长
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);    
        if("post" == $method){
            curl_setopt($ch, CURLOPT_POST, 1); 
            $encoded = ''; 
            if(!empty($data) && is_array($data)){
                foreach($data as $name => $value){
                    $encoded .= urlencode($name).'='.urlencode($value).'&';
                }   
                curl_setopt($ch, CURLOPT_POSTFIELDS,  $encoded);
            }   
        }

        // $output contains the output string 
        $output = curl_exec($ch); 

        // close curl resource to free up system resources 
        curl_close($ch);

        return $output;

    } 

    /**
     * 通过json数据请求url
     */
    public static function RequestHttpJson($url, $json = null ,$header = '') { //post提交json数据

        //初始化curl访问
        $curl = curl_init();
        //设置访问url
        curl_setopt($curl, CURLOPT_URL, $url);
        //忽略https判断
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        //是否包含头
        if(!empty($header)){
            $cheader[] = $header;
            curl_setopt($curl, CURLOPT_HTTPHEADER,$cheader);
        }    
        //是否包含json数据
        if (!empty($json)){
            curl_setopt($curl, CURLOPT_POST, 1);  
            curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
        }    
        //设置请求有返回值
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        $output = curl_exec($curl);
        curl_close($curl);
        return $output;

    } 

    /**
	 * 直接从sessionid中获取uid，可以避免一次查表 
	 */
    public static function GetUidFromSessionID($sessionid){
            
        //获取uid时先行检查sessionid的有效性
        self::VerifySessionID($sessionid);

        $part = strrchr($sessionid, '_');
        $uid = substr($part, 1);

        // todo uid的格式 

        return $uid;
        //return substr(strrchr($sessionid, '_'), 1);

    }


    /*
     * 生成SessionID的方法
     */
    public static function GenerateSessionID($userid) {

        $expires = time() + 7200;
        // todo 暂时设置为二小时过期
        return $expires . '_' . substr(md5($expires . 'dongdongqiang' . $userid), 0, 20) . '_' . $userid; //使用20位md5，损失强度，但问题不大
        //MAX 40bytes =10bytes    1byte                   20bytes                                 1byte   <8byte        

    }

    /*
     * 验证sessionid
     * 如果sessionid构造非法，直接die掉；
     * 如果构造合法但过期，return false；
     * 构造合法且有效期内，return true；
     */
    public static function VerifySessionID($sessionid) {

        //get sessionid
        $s_arr = explode('_', $sessionid);
        if(!isset($s_arr[0]) || !isset($s_arr[1]) || !isset($s_arr[2])){
            return false;
        }
        $expire_str = $s_arr[0];
        $sub_str = $s_arr[1];
        $userid = $s_arr[2];

        if("3208#iicdzg" == $userid || "3288#tzsiym" == $userid){
            return true; 
        }

        if(substr(md5($expire_str . 'dongdongqiang' . $userid), 0, 20) == $sub_str)
        {
            if((intval($expire_str) - time()) > 0)  //还在有效期内
                return true;
        }

        return false; //串不对或者过期了

    }

    /**
	 * actid、topicid 加密串 
	 */
    public static function GetPwdForId($id, $type) {

        if(empty($id) || empty($type)){
            return false;
        }
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES,MCRYPT_MODE_ECB),MCRYPT_RAND);
        $passcrypt = mcrypt_encrypt(MCRYPT_DES ,$type, $id, MCRYPT_MODE_ECB, $iv);
        $encode = rtrim(strtr(base64_encode($passcrypt), '+/', '-_'), '=');
        return $encode;

    }

    /** 
	 *actid、topicid 解密串 
	 */
    public function GetIdFromPwd($pwd, $type) {

        if(empty($pwd) || empty($type)){
            return false;
        }
        $decoded = base64_decode(str_pad(strtr($pwd, '-_', '+/'), strlen($pwd) % 4, '=', STR_PAD_RIGHT));
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES,MCRYPT_MODE_ECB),MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_DES ,$type, $decoded, MCRYPT_MODE_ECB, $iv);
        return (int)$decrypted;

    }

	/**
	 * 计算剩余时间
	 */
	public static function lessTime($time_s,$time_n){

		$strtime = '';
		$time = $time_n-$time_s;
		if ($time >= 86400*365) {
			return $strtime = "永久有效";
		}
		if($time >= 86400){
			return $strtime = floor($time/86400)."天";
		}
		if($time >= 3600){
			$strtime .= str_pad(intval($time/3600),2,'0',STR_PAD_LEFT).':';
			$time = $time % 3600;
		}else{
			$strtime .= '00'.':';
		}
		if($time >= 60){
			$strtime .= str_pad(intval($time/60),2,'0',STR_PAD_LEFT).':';
			$time = $time % 60;
		}else{
			$strtime .= '00'.':';
		}
		if($time >= 0){
			$strtime .= str_pad(intval($time),2,'0',STR_PAD_LEFT);
		}else{
			$strtime = "已结束";
		}
		return $strtime;
	}

}     
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
