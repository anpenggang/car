<?php

class Sphinx {

	private static $_instance ;

	private $_sphinx ;
	private $_host = '115.29.43.57';
	private $_port = 9312;
	public function __construct(){

		$this->_sphinx = new SphinxClient();
		$this->_sphinx->setServer($this->_host,$this->_port);
		self::$_instance = $this->_sphinx;
	}

	public static function getInstance(){

		if(is_null(self::$_instance)){
			new self();
		}
		return self::$_instance;
	} 

}
