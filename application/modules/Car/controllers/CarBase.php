<?php

/**
 *
 * @name CarBaseController
 * @desc 基类控制器，所有的控制器类都继承自本基类
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2019/8/21 新建
 **/

class CarBaseController extends BaseController
{

    /**
     * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
     */
    public function init()
    {

        parent::init();
        //本项目作为接口返回数据，关闭自动渲染视图
        Yaf_Dispatcher::getInstance()->disableView();

    }
}
