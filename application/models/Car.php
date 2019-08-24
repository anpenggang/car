<?php

/**
 *
 * @name CarModel
 * @desc Car模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Car.php v0.0 2019/08/21 新建
 */
class CarModel extends BaseModel {

    private $car_brand_table = 'car_brand'; //品牌表
    private $car_line_table = 'car_line'; //车系表
    private $car_model_table = 'car_model'; //车型表

    /**
	 * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
	 */
	public function __construct() {
		
		//调用父类构造方法
		parent::__construct();		

	}

	public function carList() {

	    $sql = "select 
                  cm.id
                  ,cm.name
                  ,cm.line_id
                  ,cm.ceiling_price
                  ,cm.floor_price
                  ,cm.floor_oil
                  ,cm.ceiling_oil
                   from car_model cm
                   where cm.deleted = 0
                  ";
        return $this->_db->rawQuery($sql);
    }

}

