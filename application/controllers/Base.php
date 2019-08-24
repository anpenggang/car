<?php

/**
 * 
 * @name BaseController
 * @desc 基类控制器，所有的控制器类都继承自本基类
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2019/8/19 新建
 **/

class BaseController extends Yaf_Controller_Abstract {

	protected $_redis = null;
	
	/**
	 * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
	 */
	public function init() {

		//实例化redis实例
		$this->_redis = Myredis::create();

		//输出头消息，防止中文乱码
		header("Content-Type:text/html;charset=utf8");		

	}

	/**
	 * 生成指定长度的密钥
	 * @param	Integer	$length	密钥的长度
	 * @return	String	$password 返回$length长度的密钥
	 */ 
	public function generatePassword($length = 12) {

		$randstr = "";
		for ($i = 0; $i < (int)$length; $i++) {
			$randnum = mt_rand(0, 51);
			if ($randnum < 26) {
				$randstr .= chr($randnum + 65); // A-Z之间字符
			} else {
				$randstr .= chr($randnum + 71); // a-z之间字符
			}
    	}

		return $randstr;

	}

	/**
	 * 生成指定长度的随机串
	 */
	public function randomStrNum($length) {

		//生成一个包含 大写英文字母, 小写英文字母, 数字 的数组
    	$arr = array_merge(range(0, 9), range('a', 'z'), range('A', 'Z'));
    	$str = '';
    	$arr_len = count($arr);
    	for ($i = 0; $i < $length; $i++)
    	{
        	$rand = mt_rand(0, $arr_len-1);
        	$str.=$arr[$rand];
    	}
    	return $str;

	}

	/**
	 * 输出xml字符（数组转换成xml）
	 * @param	Array	$params	需要转换的数组 
	 * @return	string	返回组装的xml
	 **/
	public function arrayToXml($params){

		if (!is_array($params) || count($params) <= 0) {
			return false;
		}
		$xml = "<xml>";
		foreach ($params as $key => $val) {
			if (is_numeric($val)) {
				$xml.="<".$key.">".$val."</".$key.">";
			} else {
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}
		$xml.="</xml>";
		return $xml;

	}

	/**
	 * 将xml转为array
	 * @param	String	$xml	需要转换的XML
	 * @return	Array	$data	返回组装的数组
	 */
	public function xmlToArray($xml){

		if(!$xml){
			return false;
		}
		//将XML转为array
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
		return $data;

    }

	/**
	 * 将数组转换成键值对形式的字符串:key=value&k=v
	 * @param	Array	$params	需要拼接的数组
	 * @return	String	$string	组装后的字符串
	 */
	public function toUrlParams($params){

		$string = '';
		if (!empty($params)) {
			$array = array();
			foreach( $params as $key => $value ){
				$array[] = $key.'='.$value;
			}
			$string = implode("&",$array);
		}
		return $string;

    }

	/**
	 * 验证登陆状态
	 */
	protected function verifySessionid($controller) {
			
		//验证登录状态
		$sessionid = Common_Util::getHttpReqQuery($controller,'sessionid','Str','y');//sessionid
		$userinfo = $this->_redis->get($sessionid);
        if (!$userinfo) { 
            die(Common_Util::returnJson('10003','session已经过期，请重新登录'));
        }
		return json_decode($userinfo,true);
	}

	public function ajaxReturn($status,$msg,$data = null,$data_access = null) {

        return Common_Util::returnJson($status,$msg,$data,$data_access);

    }
    public function fileUpload()
    {
        ini_set('memory_limit', '720M'); // 临时设置最大内存占用为3G
        set_time_limit(0); // 设置脚本最大执行时间 为0 永不过期

        if (!isset($_FILES['file'])) {
            return $this->ajaxReturn(-1,'暂无文件',[]);
        } else {
            $file = $_FILES['file'];
        }
        $filePath =  "/home/www/car/public/images/";
        $str = "";

        //注意设置时区
        $time = date("YmdHis");//当前上传的时间
        //获取上传文件的名称
        $filename = substr($file['name'], 0, strrpos($file['name'], '.'));
        //获取上传文件的扩展名
        $extend = strrchr($file['name'], '.');
        //上传后的文件名
        $name = $filename . $time . mt_rand(10000, 99999) . $extend;
        $uploadfile = $filePath . $name;//上传后的文件名地址
        $filetempname = $file['tmp_name'];
        $result = move_uploaded_file($filetempname, $uploadfile);//假如上传到当前目录下
        if ($result) {
            return $this->ajaxReturn(0,'ok',['url' => $filetempname]);
        }

    }

}
