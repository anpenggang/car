<?php

/**
 *
 * @name CarModel
 * @desc Car模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Car.php v0.0 2019/08/21 新建
 */
class CarModel extends BaseModel
{

    private $car_brand_table = 'car_brand'; //品牌表
    private $car_line_table = 'car_line'; //车系表
    private $car_model_table = 'car_model'; //车型表

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct()
    {

        //调用父类构造方法
        parent::__construct();

    }

    /**
     * 获取指定车系的车型列表
     *
     * @param $line_id
     * @return array
     */
    public function carModelList($line_id)
    {
        $sql = "select 
                  cm.id
                  ,cm.name
                  ,cm.short_name
                  ,features
                  ,slogan
                  ,price
                  ,cm.line_id
                  ,cm.ceiling_price
                  ,cm.floor_price
                  ,cm.floor_oil
                  ,cm.ceiling_oil
                   from car_model cm
                   where cm.deleted = 0
                   and cm.line_id = {$line_id}
                  ";
        return $this->_db->rawQuery($sql);
    }

    /**
     * 获取指定品牌下的车系列表
     *
     * @param $brand_id
     * @return array
     */
    public function carLineList($brand_id = 1)
    {

        $sql = "select 
                  cl.id
                  ,cl.name
                  ,cl.short_name
                  ,cl.ceiling_price
                  ,cl.floor_price
                  ,cl.floor_oil
                  ,cl.ceiling_oil
                  ,cl.features
                  ,cl.slogan
                  ,cl.car_type
                  ,cl.is_made_china
                   from car_line cl
                   where cl.deleted = 0
                   and brand_id = {$brand_id}
                  ";
        return $this->_db->rawQuery($sql);
    }

    //获取指定车系详情
    public function carLineDetail($line_id)
    {

        $sql = "select 
                  cl.id
                  ,cl.name
                  ,cl.short_name
                  ,cl.ceiling_price
                  ,cl.floor_price
                  ,cl.floor_oil
                  ,cl.ceiling_oil
                  ,cl.features
                  ,cl.slogan
                  ,cl.car_type
                  ,cl.is_made_china
                   from car_line cl
                   where cl.deleted = 0
                   and brand_id = {$brand_id}
                  ";
        return $this->_db->rawQuery($sql);

    }

    //获取指定车型详情
    public function carMoelDetail($line_id)
    {


    }

}

