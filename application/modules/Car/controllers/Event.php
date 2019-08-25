<?php

/**
 *
 * @name EventController
 * @desc 车辆控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version v0.0 2019/8/25 新建
 */

class EventController extends BaseController {

    private $_model = null;

    /**
     *初始化方法，用户控制器被调用的时候先执行初始化方法
     */
    public function init() {

        //调用父类的初始化方法
        parent::init();

        //初始化用户模型
        $this->_model = new EventModel();

    }

   //获取车型列表
   public function listAction() {
	$evnet_list = $this->_model->eventList();
	foreach($evnet_list as $k => $v) {
		#$evnet_list[$k]['features'] = explode("\n",$v['features']);	
		#$evnet_list[$k]['model_id'] = $v['id'];	
	}
	 return $this->ajaxReturn(0,'ok',$evnet_list);

   }
}//endclass
