<?php

/**
 *
 * @name EventController
 * @desc 车辆控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version v0.0 2019/8/25 新建
 */

class EventController extends BaseController
{

    private $_model = null;

    /**
     *初始化方法，用户控制器被调用的时候先执行初始化方法
     */
    public function init()
    {

        //调用父类的初始化方法
        parent::init();

        //初始化用户模型
        $this->_model = new EventModel();

    }

    //获取活动列表
    public function listAction()
    {
        $evnet_list = $this->_model->eventList();
        foreach ($evnet_list as $k => $v) {
            #$evnet_list[$k]['features'] = explode("\n",$v['features']);
            #$evnet_list[$k]['model_id'] = $v['id'];
        }
        return $this->ajaxReturn(0, 'ok', $evnet_list);

    }

    //获取活动详情
    public function detailAction()
    {

        $car_banner_model = new CarBannerModel();

        $event_id = Common_Util::getHttpReqQuery($this, 'event_id', 'Str', 'n');//活动标题
        $evnet_details = $this->_model->detail($event_id);
        if (!empty($evnet_details)) {
            $evnet_detail = $evnet_details[0];
            $evnet_detail['detail_img'] = $car_banner_model->getImageList(8, $evnet_detail['id']);
            return $this->ajaxReturn(0, 'ok', $evnet_detail);
        } else {
            return $this->ajaxReturn(-1, 'not find info', []);
        }

    }

    //后台添加活动
    public function addAction()
    {
        $data['title'] = Common_Util::getHttpReqQuery($this, 'title', 'Str', 'n');//活动标题
        $data['short_content'] = Common_Util::getHttpReqQuery($this, 'short_content', 'Str', 'n');//活动简介
        $data['cover_img'] = Common_Util::getHttpReqQuery($this, 'cover_img', 'Str', 'n');//封面图片
        $data['status'] = Common_Util::getHttpReqQuery($this, 'status', 'Str', 'n');//活动状态
        $data['start_time'] = Common_Util::getHttpReqQuery($this, 'start_time', 'Str', 'n');//开始时间
        $data['end_time'] = Common_Util::getHttpReqQuery($this, 'end_time', 'Str', 'n');//结束时间

        $event_img = Common_Util::getHttpReqQuery($this, 'event_img', 'Str', 'n');//活动内容照片
        $data['content'] = Common_Util::getHttpReqQuery($this, 'content', 'Str', 'n');//活动内容
        $ret = $this->_model->add($data, $event_img);

        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }

    //修改活动状态
    public function editStatusAction()
    {

        $event_id = Common_Util::getHttpReqQuery($this, 'event_id', 'Str', 'n');//活动标题
        $data['status'] = Common_Util::getHttpReqQuery($this, 'status', 'Int', 'n');//活动状态
        $data['updated_at'] = date('Y-m-d H:i:s');
        $ret = $this->_model->edit($event_id, $data);

        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }

    //删除活动
    public function deleteAction()
    {

        $event_id = Common_Util::getHttpReqQuery($this, 'event_id', 'Str', 'n');//活动标题
        $data['deleted'] = 1;//活动状态
        $data['updated_at'] = date('Y-m-d H:i:s');
        $ret = $this->_model->edit($event_id, $data);

        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }

    //编辑修改活动内容
    public function editAction()
    {

        $event_id = Common_Util::getHttpReqQuery($this, 'event_id', 'Str', 'n');//活动标题
        $data['title'] = Common_Util::getHttpReqQuery($this, 'title', 'Str', 'n');//活动标题
        $data['short_content'] = Common_Util::getHttpReqQuery($this, 'short_content', 'Str', 'n');//活动简介
        $data['cover_img'] = Common_Util::getHttpReqQuery($this, 'cover_img', 'Str', 'n');//封面图片
        $data['status'] = Common_Util::getHttpReqQuery($this, 'status', 'Str', 'n');//活动状态
        $data['start_time'] = Common_Util::getHttpReqQuery($this, 'start_time', 'Str', 'n');//开始时间
        $data['end_time'] = Common_Util::getHttpReqQuery($this, 'end_time', 'Str', 'n');//结束时间

        $event_img = Common_Util::getHttpReqQuery($this, 'event_img', 'Str', 'n');//活动内容照片
        $data['content'] = Common_Util::getHttpReqQuery($this, 'content', 'Str', 'n');//活动内容
        $data['updated_at'] = date('Y-m-d H:i:s');
        $ret = $this->_model->edit($event_id, $data, $event_img);

        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }


}//endclass
