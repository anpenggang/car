<?php

/**
 *
 * @name HelperController
 * @desc 帮助控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Helper.php v0.0 2018/3/12 新建
 */
class HelperController extends BaseController {

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

	}
	
	/**
	 * 获取帮助信息
	 */
	public function getAction() {

		$arr = [];
		$i = 0;
		foreach (Common_Const::$Ptn_helper as $key => $value) {

			$arr[$i]['title'] = $key;
			$arr[$i]['content'] = $value;
			$arr[$i]['show'] = false;
			$i++;
		}
		
		return Common_Util::returnJson('20001','查询成功',$arr);
	
	}

}//class
