<?php

/**
 * 
 * @name BaseController
 * @desc 基类控制器，所有的控制器类都继承自本基类
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2017/9/30 新建
 **/

class IndexController extends Yaf_Controller_Abstract {
    
    /**
     * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
     */
    public function init() {
        
        //本项目作为接口返回数据，关闭自动渲染视图
        Yaf_Dispatcher::getInstance()->disableView();

        //输出头消息，防止中文乱码
        header("Content-Type:text/html;charset=utf8");

    }
	public function indexAction() {

		$user_model = new UserModel();
		$res = $user_model->getUserInfo(1);
		var_dump($res);

	}
}
