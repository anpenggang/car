<?php
/**
 * @name SampleModel
 * @desc sample数据获取类, 可以访问数据库，文件，其它系统等
 * @author 
 */

class ObjectModel {

	public $client ;
	public $bucket='dingdangqd';
	public $endpoint= "http://oss-cn-qingdao.aliyuncs.com";

	//根据文件后缀名，确认content-type的内容
	private $contenttype_array = array(
		"jpg" => 'image/jpeg',	
		"jpeg" => 'image/jpeg',	
		"gif" => 'image/gif',	
		"png" => 'image/png',	
		"apk" => "application/octet-stream",
	);

	public function __construct() {

		$this->client = OssUtil::createClient($this->endpoint);
	}

	/*******************公共方法部分**********************/
	public function createPicObject($file, $size, $postfix)
	{
		return $this->_createObject($file, $size, $postfix);
	}

	public function createVideoObject($file, $size, $postfix)
	{
		return $this->_createObject($file, $size, $postfix);
	}

	public function createPkgObject($file, $size, $postfix)
	{
		return $this->_createObject($file, $size, $postfix);
	}


	private function _createObject($file, $size, $postfix)
	{
		// todo 可命名包名
		$pickey = md5( time() . rand(1000, 9999) . $this->bucket );
		if("apk" == $postfix){
			$pickey .= "." . $postfix; 
		}

		$mime = "application/octet-stream";
		if(!empty($postfix) && isset($this->contenttype_array[$postfix])){
			$mime = $this->contenttype_array[$postfix];
		}

		try{
			$ret = OssUtil::putResourceObject($this->client, $this->bucket, $pickey, file_get_contents($file), $size, $mime);

			if(FALSE !== $ret){ 
				return $pickey . "|" . $this->bucket;
		/*
		   $bucket = $this->bucket;
		   return "http://${bucket}.oss-cn-qingdao.aliyuncs.com/$pickey";
		 */
			}else{
				return FALSE;
			}
		}catch( Exception $e){
			return FALSE;
		}
	}


	/*******************************业务逻辑部分*******************************************/

}
