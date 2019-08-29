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
        $car_line_list = $this->_model->carLineDetail($line_id);
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
        $car_model_list = $this->_model->carModelList($line_id);
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


    /**
     *   `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(2048) NOT NULL DEFAULT '' COMMENT '车系名',
    `short_name` varchar(512) DEFAULT NULL COMMENT '名称简称',
    `brand_id` int(11) NOT NULL DEFAULT '0' COMMENT '品牌ID',
    `features` text COMMENT '产品亮点,使用回车分割',
    `slogan` varchar(512) DEFAULT NULL COMMENT '标语',
    `ceiling_price` int(11) NOT NULL DEFAULT '0' COMMENT '最高价(单位分)',
    `floor_price` decimal(11,0) NOT NULL DEFAULT '0' COMMENT '最低价(单位分)',
    `floor_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最高燃油(单位毫升)',
    `cover_img` int(11) DEFAULT NULL COMMENT '封面图片',
    `ceiling_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最低燃油(单位毫升)',
    `car_type_word` varchar(512) DEFAULT NULL COMMENT '车类型文字版本',
    `car_type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '车大小类型',
    `is_made_china` tinyint(4) DEFAULT '1' COMMENT '是否国产',
    `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0:未删除，1:已删除）',
     */
    //修改车系详情接口
    public function editLineDetailAction() {
        $line_id = Common_Util::getHttpReqQuery($this, 'line_id', 'Int', 'n', ''); //车系id
        $data['name'] = Common_Util::getHttpReqQuery($this, 'name', 'Str', 'n', ''); //车型名称
        $data['short_name'] = Common_Util::getHttpReqQuery($this, 'short_name', 'Str', 'n', ''); //简称
        $data['features'] = Common_Util::getHttpReqQuery($this, 'features', 'Str', 'n', ''); //描述
        $data['ceiling_price'] = Common_Util::getHttpReqQuery($this, 'ceiling_price', 'Str', 'n', ''); //最低价格
        $data['floor_price'] = Common_Util::getHttpReqQuery($this, 'floor_price', 'Str', 'n', ''); //最高价格
        $data['floor_oil'] = Common_Util::getHttpReqQuery($this, 'floor_oil', 'Str', 'n', ''); //最低燃油
        $data['ceiling_oil'] = Common_Util::getHttpReqQuery($this, 'ceiling_oil', 'Str', 'n', ''); //最高燃油
        $data['cover_img'] = Common_Util::getHttpReqQuery($this, 'cover_img', 'Str', 'n', ''); //封面图片
        $data['car_type'] = Common_Util::getHttpReqQuery($this, 'car_type', 'Int', 'n', ''); //车大小类型
        $data['is_made_china'] = Common_Util::getHttpReqQuery($this, 'is_made_china', 'Int', 'n', ''); //是否国产
        $data['brand_id'] = 1; //品牌ID
        $data['updated_at'] = date('Y-m-d H:i:s');

        $data_t['appear'] = Common_Util::getHttpReqQuery($this, 'appear', 'Str', 'n', ''); //外观
        $data_t['interior'] = Common_Util::getHttpReqQuery($this, 'interior', 'Str', 'n', ''); //内饰
        $data_t['tech'] = Common_Util::getHttpReqQuery($this, 'tech', 'Str', 'n', ''); //内饰
        $data_t['space'] = Common_Util::getHttpReqQuery($this, 'space', 'Str', 'n', ''); //空间

        $ret = $this->_model->editLineDetail($line_id,$data,$data_t);
        if ($ret) {
            return $this->ajaxReturn(0,'ok',[]);
        } else {
            return $this->ajaxReturn(-1,'error please try again',[]);
        }
    }

    //添加车型列表

    /**
     * `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '车型名称',
    `short_name` varchar(512) DEFAULT NULL COMMENT '名称简称',
    `features` text COMMENT '产品亮点,使用回车分割',
    `ceiling_price` int(11) NOT NULL DEFAULT '0' COMMENT '最高价(单位分)',
    `floor_price` int(11) NOT NULL DEFAULT '0' COMMENT '最低价(单位分)',
    `guiding_price` int(11) DEFAULT NULL COMMENT '指导价(单位分)',
    `down_payment` int(11) DEFAULT NULL COMMENT '首付款',
    `price` int(11) NOT NULL COMMENT '裸车价(单位分)',
    `slogan` varchar(512) DEFAULT NULL COMMENT '标语',
    `floor_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最高燃油(单位毫升)',
    `ceiling_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最低燃油(单位毫升)',
    `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0:未删除，1:已删除）',
    `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '车系ID',
    `remark` varchar(2048) DEFAULT NULL COMMENT '备注',
    `other_policies` text COMMENT '其他政策',
    `purchase_tax` int(11) DEFAULT NULL COMMENT '购置税',
     */
    public function addModelAction () {

        $data['line_id'] = Common_Util::getHttpReqQuery($this, 'line_id', 'Int', 'n', ''); //车系id
        $data['name'] = Common_Util::getHttpReqQuery($this, 'name', 'Str', 'n', ''); //车型名称
        $data['short_name'] = Common_Util::getHttpReqQuery($this, 'short_name', 'Str', 'n', ''); //简称
        $data['features'] = Common_Util::getHttpReqQuery($this, 'features', 'Str', 'n', ''); //描述
        $data['ceiling_price'] = Common_Util::getHttpReqQuery($this, 'ceiling_price', 'Str', 'n', ''); //最低价格
        $data['floor_price'] = Common_Util::getHttpReqQuery($this, 'floor_price', 'Str', 'n', ''); //最高价格
        $data['price'] = Common_Util::getHttpReqQuery($this, 'price', 'Str', 'n', ''); //裸车价
        $data['floor_oil'] = Common_Util::getHttpReqQuery($this, 'floor_oil', 'Str', 'n', ''); //最低燃油
        $data['ceiling_oil'] = Common_Util::getHttpReqQuery($this, 'ceiling_oil', 'Str', 'n', ''); //最高燃油
        $data['cover_img'] = Common_Util::getHttpReqQuery($this, 'cover_img', 'Str', 'n', ''); //封面图片

        $ret = $this->_model->addModel($data);
        if ($ret) {
            return $this->ajaxReturn(0,'ok',[]);
        } else {
            return $this->ajaxReturn(-1,'error please try again',[]);
        }

    }

    //修改车型信息
    public function editModelAction() {

        $model_id = Common_Util::getHttpReqQuery($this, 'model_id', 'Int', 'n', ''); //车型id
        $data['line_id'] = Common_Util::getHttpReqQuery($this, 'line_id', 'Int', 'n', ''); //车系id
        $data['name'] = Common_Util::getHttpReqQuery($this, 'name', 'Str', 'n', ''); //车型名称
        $data['short_name'] = Common_Util::getHttpReqQuery($this, 'short_name', 'Str', 'n', ''); //简称
        $data['features'] = Common_Util::getHttpReqQuery($this, 'features', 'Str', 'n', ''); //描述
        $data['ceiling_price'] = Common_Util::getHttpReqQuery($this, 'ceiling_price', 'Str', 'n', ''); //最低价格
        $data['floor_price'] = Common_Util::getHttpReqQuery($this, 'floor_price', 'Str', 'n', ''); //最高价格
        $data['price'] = Common_Util::getHttpReqQuery($this, 'price', 'Str', 'n', ''); //裸车价
        $data['floor_oil'] = Common_Util::getHttpReqQuery($this, 'floor_oil', 'Str', 'n', ''); //最低燃油
        $data['ceiling_oil'] = Common_Util::getHttpReqQuery($this, 'ceiling_oil', 'Str', 'n', ''); //最高燃油
        $data['cover_img'] = Common_Util::getHttpReqQuery($this, 'cover_img', 'Str', 'n', ''); //封面图片
        $data['updated_at'] = date("Y-m-d H:i:s");
        $ret = $this->_model->editModel($model_id,$data);
        if ($ret) {
            return $this->ajaxReturn(0,'ok',[]);
        } else {
            return $this->ajaxReturn(-1,'error please try again',[]);
        }

    }

    /**
     * `name` varchar(1024) NOT NULL DEFAULT '' COMMENT '车型名称',
    `short_name` varchar(512) DEFAULT NULL COMMENT '名称简称',
    `features` text COMMENT '产品亮点,使用回车分割',
    `ceiling_price` int(11) NOT NULL DEFAULT '0' COMMENT '最高价(单位分)',
    `floor_price` int(11) NOT NULL DEFAULT '0' COMMENT '最低价(单位分)',
    `guiding_price` int(11) DEFAULT NULL COMMENT '指导价(单位分)',
    `down_payment` int(11) DEFAULT NULL COMMENT '首付款',
    `price` int(11) NOT NULL COMMENT '裸车价(单位分)',
    `slogan` varchar(512) DEFAULT NULL COMMENT '标语',
    `floor_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最高燃油(单位毫升)',
    `ceiling_oil` int(11) NOT NULL DEFAULT '0' COMMENT '最低燃油(单位毫升)',
    `deleted` tinyint(4) unsigned NOT NULL DEFAULT '0' COMMENT '状态（0:未删除，1:已删除）',
    `line_id` int(11) NOT NULL DEFAULT '0' COMMENT '车系ID',
    `remark` varchar(2048) DEFAULT NULL COMMENT '备注',
    `other_policies` text COMMENT '其他政策',
    `purchase_tax` int(11) DEFAULT NULL COMMENT '购置税',
     */
    //编辑车型计算器后台
    public function editModelCalAction() {
        $model_id = Common_Util::getHttpReqQuery($this, 'model_id', 'Int', 'n', ''); //车型id
        $data['guiding_price'] = Common_Util::getHttpReqQuery($this, 'guiding_price', 'Int', 'n', ''); //指导价格
        $data['price'] = Common_Util::getHttpReqQuery($this, 'price', 'Int', 'n', ''); //裸车价
        $data['down_payment'] = Common_Util::getHttpReqQuery($this, 'down_payment', 'Int', 'n', ''); //首付款
        $data['remark'] = Common_Util::getHttpReqQuery($this, 'remark', 'Str', 'n', ''); //备注
        $data['other_policies'] = Common_Util::getHttpReqQuery($this, 'other_policies', 'Str', 'n', ''); //其他优惠
        $data['purchase_tax'] = Common_Util::getHttpReqQuery($this, 'purchase_tax', 'Str', 'n', ''); //购置税
        $stage_info = Common_Util::getHttpReqQuery($this, 'stage_info', 'Str', 'n', ''); //分期信息

        $data['updated_at'] = date("Y-m-d H:i:s");
        $ret = $this->_model->editModelCal($model_id,$data,$stage_info);
        if ($ret) {
            return $this->ajaxReturn(0,'ok',[]);
        } else {
            return $this->ajaxReturn(-1,'error please try again',[]);
        }

    }

    //获取车型计算器
    public function getModelCalAction() {
        $model_id = Common_Util::getHttpReqQuery($this, 'model_id', 'Int', 'n', ''); //车型id
        $ret = $this->_model->getModelCal($model_id);
        return $this->ajaxReturn(0,'ok',$ret);
    }

    //单独传递图片的接口
    public function addImageAction() {

        $car_model = new CarModel();
        $origin_id = Common_Util::getHttpReqQuery($this, 'origin_id', 'Int', 'n'); //车型id
        $type = Common_Util::getHttpReqQuery($this, 'type', 'Int', 'n'); //车型id
        $image_info = Common_Util::getHttpReqQuery($this, 'image_info', 'Str', 'n'); //外观
        $ret = $car_model->processAllImage($origin_id,$type,$image_info);
        if ($ret) {
            return $this->ajaxReturn(0,'ok',[]);
        } else {
            return $this->ajaxReturn(-1,'error please try again',[]);
        }

    }


}//endclass
