<?php

/**
 * @name Event.php
 * @desc Car_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CarUser.php v0.0 2019/8/19 新建
 */
class EventModel extends BaseModel {

    private $_table = 'car_event';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct() {

        //调用父类的构造方法
        parent::__construct();

    }

    public function eventList() {

    }


}
