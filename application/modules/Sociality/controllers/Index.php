<?php

/**
 * @name IndexController
 * @desc 社交首页控制器
 * @Author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Activity.php 0.0 2018/7/17 创建
 */
class IndexController extends BaseController {

	private $_model = null; //社交模型

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();
		//实例化IndexModel()
		//$this->_model = new IndexModel();

	}
	
	public function indexAction() {

		//echo "社交模型";
		$taskid = 1;
		$out = popen("ls -al", "r");
		//var_dump($out);
		$line = fread($out,512);
		echo ($line);
        pclose($out);
        sleep(1);

	}
}
