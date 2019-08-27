<?php

/**
 *
 * @name CarController
 * @desc 车辆控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version v0.0 2019/8/21 新建
 */

class CarController extends BaseController
{

    private $_model = null;
    private $_car_banner_model = null;

    /**
     *初始化方法，用户控制器被调用的时候先执行初始化方法
     */
    public function init()
    {
        //调用父类的初始化方法
        parent::init();

        //初始化用户模型
        $this->_model = new CarModel();
        $this->_car_banner_model = new CarBannerModel();
    }

    //获取车系列表
    public function carLineListAction()
    {
        //首页列表
        $car_line_list = $this->_model->carLineList();
        foreach ($car_line_list as $k => $v) {
            $car_line_list[$k]['features'] = $this->explodeFeature($v['features']);
            $car_line_list[$k]['line_id'] = $v['id'];
            $car_line_list[$k]['banner_img'] = $this->_car_banner_model->getImageList(2, $v['id']);
            $car_line_list[$k]['tab_1'] = $this->carModelList($v['id']);
            $car_line_list[$k]['tab_2'] = $this->_car_banner_model->getImageList(4, $v['id']);
            $car_line_list[$k]['tab_3'] = $this->_car_banner_model->getImageList(5, $v['id']);
            $car_line_list[$k]['tab_4'] = $this->_car_banner_model->getImageList(6, $v['id']);
            $car_line_list[$k]['tab_5'] = $this->_car_banner_model->getImageList(7, $v['id']);
        }
        return $this->ajaxReturn(0, 'ok', $car_line_list);
    }

    //获取指定的车系详情
    public function carLineDetailAction() {

        $line_id = Common_Util::getHttpReqQuery($this, 'line_id', 'Int', 'n', ''); //车系id
        //首页列表
        $car_line_list = $this->_model->carLineList();
        foreach ($car_line_list as $k => $v) {
            $car_line_list[$k]['features'] = $this->explodeFeature($v['features']);
            $car_line_list[$k]['line_id'] = $v['id'];
            $car_line_list[$k]['banner_img'] = $this->_car_banner_model->getImageList(2, $v['id']);
            $car_line_list[$k]['tab_1'] = $this->carModelList($v['id']);
            $car_line_list[$k]['tab_2'] = $this->_car_banner_model->getImageList(4, $v['id']);
            $car_line_list[$k]['tab_3'] = $this->_car_banner_model->getImageList(5, $v['id']);
            $car_line_list[$k]['tab_4'] = $this->_car_banner_model->getImageList(6, $v['id']);
            $car_line_list[$k]['tab_5'] = $this->_car_banner_model->getImageList(7, $v['id']);
        }
        return $this->ajaxReturn(0, 'ok', $car_line_list);
    }

    //获取车型列表
    public function carModelListAction()
    {
        $line_id = Common_Util::getHttpReqQuery($this, 'line_id', 'Int', 'n', ''); //车系id
        $car_model_list = $this->carModelList($line_id);
        return $this->ajaxReturn(0, 'ok', $car_model_list);

    }

    private function carModelList($line_id)
    {
        $car_model_list = $this->_model->carModel($line_id);
        foreach ($car_model_list as $k => $v) {
            $car_model_list[$k]['features'] = $this->explodeFeature($v['features']);;
            $car_model_list[$k]['model_id'] = $v['id'];
        }
        return $car_model_list;

    }

    //品牌列表
    public function carBrandListAction()
    {
        $origin_img = $this->_car_banner_model->getImageList(11, 1);
        $history_img = $this->_car_banner_model->getImageList(12, 1);
        $feature_img = $this->_car_banner_model->getImageList(13, 1);

        return $this->ajaxReturn(0, 'ok', [
            'origin_info' => $origin_img,
            'history_info' => $history_img,
            'feature_info' => $feature_img,
        ]);

    }

    //feature拆分方法
    public function explodeFeature($content) {

        return explode("\n", $content);

    }


}//endclass
