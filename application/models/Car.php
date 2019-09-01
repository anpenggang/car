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
                   and id = {$line_id}
                  ";
        return $this->_db->rawQuery($sql);

    }

    //获取指定车型详情
    public function carMoelDetail($line_id)
    {


    }

    //新增车型
    public function addModel($data)
    {

        $ret = $this->_db->insert($this->car_model_table, $data);
        return $ret;

    }
    //修改车型
    public function editModel($model_id,$data) {

        $this->_db->where('id',$model_id);
        $ret = $this->_db->update($this->car_model_table,$data);
        return $ret;

    }

    //编辑车系详情
    public function editLineDetail($line_id, $data, $data_t = '')
    {

        try {
            //开启事务
            $this->_db->autocommit(false);
            $this->_db->where('id',$line_id);
            $ret = $this->_db->update($this->car_line_table, $data);
            if (!$ret) {
                //回滚
                $this->_db->rollback();
                return false;
            }
//            //外观
//            $image_appear_ret = $this->processImage($this->_db, 4, $line_id, $data_t['appear']);
//            if (!$image_appear_ret) {
//                //回滚
//                $this->_db->rollback();
//                return false;
//            }
//            //内饰
//            $image_interior_ret = $this->processImage($this->_db, 5, $line_id, $data_t['interior']);
//            if (!$image_interior_ret) {
//                //回滚
//                $this->_db->rollback();
//                return false;
//            }
//            //科技
//            $image_tech_ret = $this->processImage($this->_db, 6, $line_id, $data_t['tech']);
//            if (!$image_tech_ret) {
//                //回滚
//                $this->_db->rollback();
//                return false;
//            }
//            //空间
//            $image_space_ret = $this->processImage($this->_db, 7, $line_id, $data_t['space']);
//            if (!$image_space_ret) {
//                //回滚
//                $this->_db->rollback();
//                return false;
//            }
            $this->_db->commit();
            return $line_id;

        } catch (Exception $e) {
            Common_Util::write_log('code -2 编辑车辆详情失败' . $e->getMessage());
        }

    }

    //处理车系图片
    public function processImage($connection, $type, $origin_id, $event_img)
    {

        if (!empty($event_img)) {//添加活动照片
            $connection->where('type', $type)->where('origin_id', $origin_id);
            $connection->delete('car_image');
            $event_img = $this->front_json_decode($event_img);
            //print_r($event_img);
            if (is_array($event_img)) {
                foreach ($event_img as $key => $value) {
                    $insert_data = [
                        'origin_id' => $origin_id,
                        'type' => $type,
                        'img_src' => $value['img_src'],
                        'remark' => $value['remark'],
                        'width' => $value['width'],
                        'height' => $value['height'],
                    ];
                    $insert_ret = $connection->insert('car_image', $insert_data);
                    #var_dump($insert_ret);
                    #$insert_ret = $this->_db->insert('car_image', $insert_data);
                    if (!$insert_ret) {
                        //回滚
                        $connection->rollback();
                        return false;
                    }
                }
            }
        }

        return true;

    }

    //修改车型计算器
    public function editModelCal($model_id,$data,$stage_info) {

        try {
            //开启事务
            $this->_db->autocommit(false);
            $this->_db->where('id',$model_id);
            $ret = $this->_db->update($this->car_model_table, $data);
            if (!$ret) {
                //回滚
                $this->_db->rollback();
                return false;
            }
            //外观
            if (!empty($stage_info)) {
                $stage_info_arr = $this->front_json_decode($stage_info);
                $this->_db->where('model_id',$model_id);
                $this->_db->delete('car_model_stage');
                if (is_array($stage_info_arr)) {
                    foreach ($stage_info_arr as $key => $value) {
                        $insert_data = [
                            'stages_times' => $value['stages_times'],
                            'stage_interest' => $value['stage_interest'],
                            'model_id' => $model_id,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ];
                        $insert_ret = $this->_db->insert('car_model_stage', $insert_data);
                        if (!$insert_ret) {
                            //回滚
                            $this->_db->rollback();
                            return false;
                        }
                    }
                }
            }

            $this->_db->commit();
            return $model_id;

        } catch (Exception $e) {
            Common_Util::write_log('code -2 编辑车辆详情失败' . $e->getMessage());
        }
    }

    //删除车型
    public function carModelDelete($model_id) {

        $this->_db->where('id',$model_id);
        $ret = $this->_db->update($this->car_model_table,['deleted' => 1]);
        return $ret;

    }

    //获取车型计算器信息
    public function getModelCal($model_id) {

        $cal_infos_sql = "select
                id as model_id
                ,guiding_price
                ,down_payment
                ,price
                ,remark
                ,other_policies
                ,purchase_tax
                from car_model
                where id = $model_id";
        $cal_infos = $this->_db->rawQuery($cal_infos_sql);
        if (!empty($cal_infos)) {
            $cal_info = $cal_infos[0];
            $stage_infos_sql = "select
                    id as stage_id
                    ,model_id
                    ,stages_times
                    ,stage_interest
                    from car_model_stage
                    where model_id = $model_id
                      and deleted = 0";
            $stage_infos = $this->_db->rawQuery($stage_infos_sql);
            $cal_info['stage_info'] = $stage_infos;
            return $cal_info;
        } else {
            return [];
        }

    }

    //处理各种不同类型的图片
    public function processAllImage($origin_id, $type, $img)
    {
        //开启事务
        $this->_db->autocommit(false);
        if (!empty($img)) {//添加活动照片
            $this->_db->where('type', $type)->where('origin_id', $origin_id);
            $this->_db->delete('car_image');
            $img_arr = $this->front_json_decode($img);
            if (is_array($img_arr)) {
                foreach ($img_arr as $key => $value) {
                    $insert_data = [
                        'origin_id' => $origin_id,
                        'type' => $type,
                        'img_src' => $value['img_src'],
                        'remark' => $value['remark'],
                        'width' => $value['width'],
                        'height' => $value['height'],
                    ];
                    $insert_ret = $this->_db->insert('car_image', $insert_data);
                    #var_dump($insert_ret);
                    #$insert_ret = $this->_db->insert('car_image', $insert_data);
                    if (!$insert_ret) {
                        //回滚
                        $this->_db->rollback();
                        return false;
                    }
                }
            }
        }
        $this->_db->commit();
        return true;

    }

}