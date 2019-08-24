<?php

/**
 *
 * @name IndexController
 * @desc 首页控制器
 * @author leslie
 * @link mailto:lesliedream@outlook.com
 * @version Index.php v0.0 2019/8/21 新建
 */
class IndexController extends CarBaseController {

	/**
	 * 初始化方法
	 */
	public function init() {
	
		//调用父类的初始化方法
		parent::init();
		
	}
	
	/**
	 * 首页方法
	 */
	public function indexAction() {

		$config = new Yaf_Config_Ini(APPLICATION_PATH."/conf/application.ini",'product');
		echo "<pre>";
		print_r($config);

	}

	/**
	 * 测试方法
	 */
	public function testAction() {

		$course_model = new LctCourseModel();
		echo json_encode($course_model->getPurchasedRecommends(10001),JSON_UNESCAPED_UNICODE);

	}

}//endclass
