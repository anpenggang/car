<?php

/**
 *
 * @name CarBanner.php
 * @desc Car_Banner Banner模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CarBanner.php v0.0 2019/8/19 新建
 */
class CarBannerModel extends BaseModel
{

    private $_table = 'car_image';

    /**
     * 构造方法,调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct()
    {

        //调用父类的构造方法
        parent::__construct();

    }

    /**
     * 获取Banner列表
     */
    public function getBannerList()
    {

        $sql = "
		        select 
		        ci.id
		        ,ci.img_src
		        ,ci.remark
		        ,ci.width
		        ,ci.height
		        from car_image ci
		        where ci.deleted = 0 -- 未删除
		        and ci.type = 1 -- 首页banner
		        order by ci.weight desc
		    ";
        return $this->_db->rawQuery($sql);
    }

    /**
     * 获取不同类型对应的图片
     * @param $type
     * @param $origin_id
     * @return array
     */
    public function getImageList($type, $origin_id = 0)
    {

        $sql = "
		        select
		        ci.id 
		        ,ci.img_src
		        ,ci.remark
		        ,ci.width
		        ,ci.height
		        from car_image ci
		        where ci.deleted = 0 -- 未删除
			and origin_id = {$origin_id}
		        and ci.type = {$type}
		        order by ci.id asc 
		    ";

        $ret = $this->_db->rawQuery($sql);
        return $ret;

    }

    /**
     * 添加图片
     * @param $connection
     * @param $insert_data
     * @return boolean
     */
    public function addImage($connection,$insert_data) {

        $insert_ret = $connection->insert($this->_table, $insert_data);

        return $insert_ret;
    }

}
