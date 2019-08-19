<?php

/**
 *
 * @name GoodsController
 * @desc 商品控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Goods.php v0.0 2018/5/28 新建
 */
 class GoodsController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化商品类模型
		$this->_model = new PtnAdviseModel();

		//根据sessionID获取用户ID
		$this->_userinfo = $this->verifySessionid($this);

	}

	public function indexAction() {

		echo 123;
	}


	public function listAction() {

		echo "商品列表";

	}

	

}
