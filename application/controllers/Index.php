<?php

/**
 *
 * @name IndexController
 * @desc 基类控制器，所有的控制器类都继承自本基类
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Base.php v0.0 2019/8/19 新建
 **/

class IndexController extends BaseController {

    protected $_redis = null;

    /**
     * 初始化方法 基类控制器被调用的时候先执行初始化方法，可作用于全局
     */
    public function init() {
        parent::init();
    }

    public function indexAction() {

    }
    public function testAction() {

    }
    public function fileAction() {
        $this->fileUpload();
        exit;
    }


}
