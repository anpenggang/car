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
        $car_model = new CarModel();
        //开启事务
        $this->_db->autocommit(false);
        $event_id = $this->_db->insert($this->_table, $data);
        if (!$event_id) {
            //回滚
            $this->_db->rollback();
            return false;
        }

        $image_ret = $car_model->processImage($this->_db, 8, $event_id, $event_img);

        if (!$image_ret) {
            //回滚
            $this->_db->rollback();
            return false;
        }
        $this->_db->commit();
        return $event_id;

    }

    //修改活动状态
    public function editStatus($event_id,$data) {
        $this->_db->where('id',$event_id);
        return $this->_db->update($this->_table, $data);
    }

    //修改活动内容
    public function edit($event_id, $data, $event_img)
    {
        $car_model = new CarModel();
        //开启事务
        $this->_db->autocommit(false);
        $this->_db->where('id',$event_id);
        $ret = $this->_db->update($this->_table, $data);
        if ($ret) {
            $image_ret = $car_model->processImage($this->_db, 8, $event_id, $event_img);

            if (!$image_ret) {
                //回滚
                $this->_db->rollback();
                return false;
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
