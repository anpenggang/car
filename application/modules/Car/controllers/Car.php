<?php

/**
 *
 * @name CarController
 * @desc 车辆控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version v0.0 2019/8/21 新建
 */

class CarController extends BaseController {

    private $_model = null;

    /**
     *初始化方法，用户控制器被调用的时候先执行初始化方法
     */
    public function init() {

        //调用父类的初始化方法
        parent::init();

        //初始化用户模型
        $this->_model = new CarModel();

    }

    /**
     * 车辆列表控制器
     */
    public function carListAction() {

        //首页列表
        $car_list = $this->_model->carList();
        return $this->ajaxReturn(0,'ok',$car_list);

    }
}//endclass
