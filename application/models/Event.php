<?php

/**
 * @name Event.php
 * @desc Car_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CarUser.php v0.0 2019/8/19 新建
 */
class EventModel extends BaseModel
{

    private $_table = 'car_event';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct()
    {

        //调用父类的构造方法
        parent::__construct();

    }

    //活动列表
    public function eventList()
    {
        $sql = "select ce.id as event_id
            ,ce.title
            ,ce.start_time
            ,ce.end_time
            ,ce.content
            ,ce.cover_img
            ,ce.status
            from car_event ce
            where ce.deleted = 0
            ";
        return $this->_db->rawQuery($sql);

    }

    //活动详情
    public function detail($event_id)
    {

        $sql = "select
                ce.id as event_id
                ,ce.title
                ,ce.start_time
                ,ce.end_time
                ,ce.content
                ,ce.cover_img
                ,ce.status
                from car_event ce
                where ce.deleted = 0
                and id = $event_id";
        return $this->_db->rawQuery($sql);
    }

    //添加活动
    public function add($data, $event_img)
    {
        $image_model = new CarBannerModel();
        //开启事务
        $this->_db->autocommit(false);
        $event_id = $this->_db->insert($this->_table, $data);
        if ($event_id) {
            $event_img = json_decode($event_img, true);
            if (!empty($event_img)) {//添加活动照片
                foreach ($event_img as $key => $value) {
                    //echo $value['menu_value'];exit;
                    $insert_data = [
                        'origin_id' => $event_id,
                        'type' => 8,
                        'img_src' => $value['img_src'],
                        'remark' => $value['remark'],
                        'width' => $value['width'],
                        'height' => $value['height'],
                    ];
                    $insert_ret = $image_model->addImage($this->_db,$insert_data);
                    #$insert_ret = $this->_db->insert('car_image', $insert_data);
                    if (!$insert_ret) {
                        //回滚
                        $this->_db->rollback();
                        return false;
                    }
                }
            }
        } else {
            //回滚
            $this->_db->rollback();
            return false;
        }
        $this->_db->commit();
        return $event_id;

    }

    //修改活动内容
    public function edit($event_id, $data, $event_img)
    {
        $this->_db->where('id', $event_id);
        $ret = $this->_db->update($this->_table, $data);
        return $ret;

        //开启事务
        $this->_db->autocommit(false);
        $ret = $this->_db->update($this->_table, $data);
        if ($ret) {
            $this->_db->where('type', 8)->where('origin_id');
            $img_delete = $this->_db->delete('car_image');
            if (!$img_delete) {
                //回滚
                $this->_db->rollback();
                return false;
            }
            $event_img = json_decode($event_img, true);
            if (!empty($event_img)) {//添加活动照片
                foreach ($event_img as $key => $value) {
                    $insert_data = [
                        'origin_id' => $event_id,
                        'type' => 8,
                        'img_src' => $value['img_src'],
                        'remark' => $value['remark'],
                        'width' => $value['width'],
                        'height' => $value['height'],
                    ];
                    $insert_ret = $image_model->addImage($this->_db,$insert_data);
                    #$insert_ret = $this->_db->insert('car_image', $insert_data);
                    if (!$insert_ret) {
                        //回滚
                        $this->_db->rollback();
                        return false;
                    }
                }
            }
        } else {
            //回滚
            $this->_db->rollback();
            return false;
        }

        $this->_db->commit();
        return $event_id;
    }


}
