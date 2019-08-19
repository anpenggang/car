<?php


class Service_Sms{

	/**
	 * 创蓝通道
	 */
	public static function Chuanglan($phone,$content){

		$post_data = array();
		$post_data['account'] = iconv('GB2312', 'GB2312',"vip_hbxy");
		$post_data['pswd'] = iconv('GB2312', 'GB2312',"Tch123456");
		$post_data['mobile'] = $phone ;
		//$post_data['needstatus'] = true ;
		$post_data['msg']=mb_convert_encoding("$content",'UTF-8', 'auto');
		$url='http://222.73.117.158/msg/HttpBatchSendSM?';
		$o="";
		foreach ($post_data as $k=>$v)
		{
			$o.= "$k=".urlencode($v)."&";
		}
		$post_data=substr($o,0,-1);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($ch);
		$co=substr($result,15,1);
		return $co == 0 ?1:0;
	}

	/**
	 * weblink51通道
	 */
	public static function weblink51($phone , $content){

		$target = "http://cf.51welink.com/submitdata/Service.asmx/g_Submit";
		$data = "sname=dlaotezh&spwd=6lTHg6Wc&scorpid=&sprdid=1012818&sdst=$phone&smsg=";
		$data .= rawurlencode('【黑白校园】'.$content);
		$url_info = parse_url($target);
		$httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
		$httpheader .= "Host:" . $url_info['host'] . "\r\n";
		$httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
		$httpheader .= "Content-Length:" . strlen($data) . "\r\n";
		$httpheader .= "Connection:close\r\n\r\n";
		$httpheader .= $data;

		$fd = fsockopen($url_info['host'], 80);
		fwrite($fd, $httpheader);
		$gets = "";
		while(!feof($fd)) {
			$gets .= fread($fd, 128);
		}
		fclose($fd);
		if($gets != ''){
			$start = strpos($gets, '<?xml');
			if($start > 0) {
				$gets = substr($gets, $start);
			}
		}
		$ret = simplexml_load_string($gets);
		if(isset($ret->State) && $ret->State == 0){
			return 1;
		}else{
			return 0;
		}
	}

}
