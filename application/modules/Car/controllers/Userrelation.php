<?php

/**
 *
 * @name UserRelationController
 * @desc Banner控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Banner.php v0.0 2019/08/19 新建
 */
class UserrelationController extends BaseController
{

    private $_model = null;
    private $_userinfo = null;

    /**
     * 初始化方法
     */
    public function init()
    {

        //调用父类的初始化方法
        parent::init();

        //实例化课程模型
        //$this->_model = new CarBannerModel();

        //根据sessionID获取用户id
        $this->_userinfo = $this->verifySessionid($this);

    }

    /**
     * 获取banner
     */
    public function addUserPhoneAction()
    {
        $phone = Common_Util::getHttpReqQuery($this, 'phone', 'Str', 'n');//用户手机号
        $user_id = $this->_userinfo['user_id'];
        $user_model = new CarUserModel();
        $ret = $user_model->addUserPhone($user_id, $phone);
        if ($ret) {
            return $this->ajaxReturn(0, 'ok', ['phone' => $phone]);
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }

    }

    /**
     * 用户参与活动
     */
    public function userAddEventAction() {
        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $event_id = Common_Util::getHttpReqQuery($this, 'event_id', 'Int', 'n');//用户手机号
        $ret = $user_model->addUserEvent($user_id,$event_id);

        if ($ret == '不可重复参加') {
            return $this->ajaxReturn(-1, '不可重复参加');
        } else {
            if ($ret) {
                return $this->ajaxReturn(0, 'ok');
            } else {
                return $this->ajaxReturn(-1, 'error please try again');
            }
        }
    }

    //获取用户参与过那些活动的接口
    public function getUserEventsAction() {

        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $ret = $user_model->getUserEvents($user_id);

        return $this->ajaxReturn(0,'ok',$ret);
    }

    //用户参与购车计算
    public function userAddModelAction() {

        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $model_id = Common_Util::getHttpReqQuery($this, 'model_id', 'Int', 'n');//用户手机号
        $price = Common_Util::getHttpReqQuery($this, 'price', 'Int', 'n');//用户手机号
        $is_stage = Common_Util::getHttpReqQuery($this, 'is_stage', 'Int', 'n');//用户手机号
        $stage_times = Common_Util::getHttpReqQuery($this, 'stage_times', 'Int', 'n');//用户手机号
        $stage_interest = Common_Util::getHttpReqQuery($this, 'stage_interest', 'Int', 'n');//用户手机号

        $ret = $user_model->userAddModel($user_id,$model_id,$price,$is_stage,$stage_times,$stage_interest);
        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }

    //获取用户参与的购车计划
    public function getUserModelAction() {
        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $ret = $user_model->getUserModel($user_id);

        return $this->ajaxReturn(0, 'ok',$ret);

    }

    //用户参与互动
    public function addUserInteractAction() {

        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $interact_id = Common_Util::getHttpReqQuery($this, 'interact_id', 'Int', 'n');//用户手机号

        $ret = $user_model->addUserInteract($user_id,$interact_id);
        if ($ret === -2) {
            return $this->ajaxReturn(-2, '已参加过互动，无须参与');
        }
        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }

    }

    //获取用户是否参与过互动
    public function getUserInteractedAction() {
        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $interact_id = Common_Util::getHttpReqQuery($this, 'interact_id', 'Int', 'n');//用户手机号

        $ret = $user_model->getUserInteract($user_id,$interact_id);

        if (empty($ret)) {
            return $this->ajaxReturn(0, '未参与互动');
        } else {
            return $this->ajaxReturn(-1, '已经参与过互动');
        }
    }

    //用户中奖页面接口
    public function addIsLuckedUserAction() {
        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $interact_id = Common_Util::getHttpReqQuery($this, 'interact_id', 'Int', 'n');//用户手机号
        $is_luck = Common_Util::getHttpReqQuery($this, 'is_luck', 'Int', 'n');//用户手机号

        $ret = $user_model->addLuckedUser($user_id,$interact_id,$is_luck);

        if ($ret === -2) {
            return $this->ajaxReturn(-2, '已参与抽奖不可重复参与');
        }
        if ($ret === -3) {
            return $this->ajaxReturn(-2, '未参与互动不能参与抽奖');
        }
        if ($ret) {
            return $this->ajaxReturn(0, 'ok');
        } else {
            return $this->ajaxReturn(-1, 'error please try again');
        }
    }

    //获取用户是否中奖
    public function getIsLuckedUserAction() {
        $user_model = new CarUserModel();
        $user_id = $this->_userinfo['user_id'];
        $interact_id = Common_Util::getHttpReqQuery($this, 'interact_id', 'Int', 'n');//用户手机号

        $ret = $user_model-> getLuckedUser($user_id,$interact_id);

        if (!empty($ret)) {
            return $this->ajaxReturn(0, 'ok',$ret);
        } else {
            return $this->ajaxReturn(-1, '本次活动未中奖');
        }
    }



}//endclass
