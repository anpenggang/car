<?php

/**
 * redis单例类
 */
final class Myredis {

	//保存当前单例类的实例
	private static $_myredis = null;

	//私有化构造方法，不允许外部实例化	
	private function __construct() {
        $config = new Yaf_Config_Ini( APPLICATION_PATH . "/conf/application.ini", 'product');
		$redis = new \Redis();
		$redis->connect($config->redis->host,$config->redis->port);
		//$redis->auth('jiangfengloveheibaixiaoyuan');
		self::$_myredis = $redis;
 	}

	//创建当前类的实例
	public static function create() {
		if (self::$_myredis === null) {
			self::$_myredis = new self();
		}
		return self::$_myredis;
	}
	//私有化克隆方法，不允许外部复制
	private function __clone() {
	
	}

	public function get($key) {
		return $this->_myredis->get($key);
	}
	
	public function set($key, $value) {
		return $this->_myredis->set($key,$value);
	}

	public function setex($key, $expire, $value) {
		return $this->_myredis->setex($key,$expire,$value);
	}
	public function del($key){
		return $this->_myredis->del($key);
	}

}
