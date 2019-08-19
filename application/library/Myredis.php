<?php

/**
 * redis单例类
 */
final class Myredis {

	//保存当前单例类的实例
	private static $_myredis = null;

	//私有化构造方法，不允许外部实例化	
	private function __construct() {
		$redis = new \Redis();
		$redis->connect('127.0.0.1','6379');
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
