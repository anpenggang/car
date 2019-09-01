<?php

/**
 * @name CarUser.php
 * @desc Car_user 用户模型
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version CarUser.php v0.0 2019/8/19 新建
 */
class CarUserModel extends BaseModel
{

    private $_table = 'user_info';

    /**
     * 构造方法，调用基类BaseModel的构造方法获取数据库连接实例
     */
    public function __construct()
    {

        //调用父类的构造方法
        parent::__construct();

    }

    /**
     * 根据openID,获取用户信息
     *
     * @param    String    openid    openid
     * @return    Array    $ret    用户信息
     */
    public function getUserByOpenid($openid)
    {

        $this->_db->where('openid', $openid);
        $ret = $this->_db->getOne($this->_table);
        return $ret;
    }

    /**
     * 添加用户
     *
     * @param    Array $data 新建用户信息
     * @return    Mixed    $ret    如果成功返回新增用户ID，失败返回false
     */
    public function addUser($data)
    {

        return $this->_db->insert($this->_table, $data);

    }

    /**
     * 更新用户头像和昵称
     *
     * @param    String    openid    openid
     * @param    Array $data 用户信息
     * @return    Mixed    $ret    如果成功返回用户信息，失败返回false
     */
    public function updateUserInfo($openid, $data)
    {

        $this->_db->where('openid', $openid);
        $ret = $this->_db->update($this->_table, $data);
        if ($ret) {
            $ret = $this->getUserByOpenid($openid);
        }
        return $ret;

    }

    /**
     * 获取用户信息
     *
     * @param    Integer $id 用户id
     * @return    Array    $ret    用户信息
     */
    public function getUserInfo($id)
    {

        $this->_db->where('id', $id);
        $ret = $this->_db->getOne($this->_table);
        return $ret;

    }

    //添加用户手机号
    public function addUserPhone($user_id, $phone)
    {

        $this->_db->where('id', $user_id);
        $ret = $this->_db->update($this->_table, ['phone' => $phone, 'updated_at' => date('Y-m-d H:i:s')]);
        return $ret;

    }

    //用户活动信息关联
    public function addUserEvent($user_id,$event_id) {
        $this->_db->where('user_id',$user_id)
            ->where('event_id',$event_id)
            ->where('deleted',0);
        $is_added = $this->_db->getOne('user_event');
        if (!empty($is_added)) {
            return '不可重复参加';
        }
        $ret = $this->_db->insert('user_event',[
            'event_id' => $event_id,
            'user_id' => $user_id,
        ]);
        return $ret;

    }

    //获取用户关联过哪些活动
    public function getUserEvents($user_id) {

        $sql = "
             select 
             ue.user_id
             ,ue.event_id
             ,ue.created_at
             ,ce.title
             ,ce.cover_img
             ,ce.start_time
             ,ce.end_time
             ,ce.status
             from user_event ue
             left join car_event ce on ce.id = ue.event_id
             where ue.user_id = $user_id
             and ue.deleted = 0
             and ce.deleted = 0
            ";
        return $this->_db->rawQuery($sql);

    }

    //用户车型关联
    public function userAddModel($user_id,$model_id,$price,$is_stage,$stage_times,$stage_interest) {

        $this->_db->where('user_id',$user_id)
            ->where('model_id',$model_id)
            ->where('deleted',0);
        $is_added = $this->_db->getOne('user_model');
        if (!empty($is_added)) {
            $this->_db->where('user_id',$user_id)
                ->where('model_id',$model_id)
                ->where('deleted',0);
            $ret = $this->_db->update('user_model',[
                'price' => $price,
                'is_stage' => $is_stage,
                'stage_times' => $stage_times,
                'stage_interest' => $stage_interest,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            $ret = $this->_db->insert('user_model',[
                'user_id' => $user_id,
                'model_id' => $model_id,
                'price' => $price,
                'is_stage' => $is_stage,
                'stage_times' => $stage_times,
                'stage_interest' => $stage_interest,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $ret;

    }

    //获取用户参与的购车计算
    public function getUserModel($user_id) {
        $sql = "select
                um.id
                ,um.user_id
                ,um.model_id
                ,um.price
                ,um.is_stage
                ,um.stage_times
                ,um.stage_interest
                ,um.created_at
                ,cm.name as model_name
                ,features as model_features
                from user_model um
                left join car_model cm on cm.id = um.model_id
                where um.user_id = $user_id
                and um.deleted = 0
                and cm.deleted = 0
                ";
        $ret = $this->_db->rawQuery($sql);
        return $ret;

    }

    //获得登录用户列表
    public function getUserList() {

        $sql = "select
                    id
                    ,nickname
                    ,gender
                    ,city
                    ,phone
                    ,avatar_url
                    ,created_at
                    ,updated_at
                    from user_info";
        $ret = $this->_db->rawQuery($sql);
        return $ret;

    }

    //更新用户手机号
    public function updateUserPhone($openid,$phoneNumber){

        $this->_db->where('openid',$openid);
        $ret = $this->_db->update($this->_table,[
            'phone' => $phoneNumber,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $ret;

    }

    //用户参与互动接口
    public function addUserInteract($user_id,$interact_id) {

        $is_added = $this->getUserInteract($user_id,$interact_id);
        if (!empty($is_added)) {
            return -2;
        } else {
            $ret = $this->_db->insert('user_interact',[
                'user_id' => $user_id,
                'interact_id' => $interact_id,
            ]);
            return $ret;
        }
    }

    //获取用户是否参与过某次互动
    public function getUserInteract($user_id,$interact_id) {
        $this->_db->where('user_id',$user_id)->where('interact_id',$interact_id);
        $is_added = $this->_db->getOne('user_interact');
        return $is_added;
    }

    //添加中奖用户接口
    public function addLuckedUser($user_id,$interact_id,$is_luck) {

        if(empty($this->getUserInteract($user_id,$interact_id))) {
            return -3;//未参与互动不能参与抽奖
        }

        if (!empty($this->getLuckedUser($user_id,$interact_id))) {
            return -2; //已参与抽奖不可重复参与
        }

        $this->_db->where('user_id',$user_id)->where('interact_id',$interact_id);
        $ret = $this->_db->update('user_interact',[
            'islucked' => $is_luck,
            'updated_at' => date('Y-m-d H:i:s')]);
        return $ret;


    }

    public function getLuckedUser($user_id,$interact_id) {

        $sql = "select 
              user_id
              ,interact_id
              ,islucked 
              from user_interact 
              where user_id= $user_id 
                and interact_id=$interact_id 
                and islucked !=0";
        return $this->_db->rawQuery($sql);


    }

}
