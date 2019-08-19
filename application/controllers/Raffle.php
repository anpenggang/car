<?php

/**
 *
 * @name RaffleController
 * @desc 抽奖控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com
 * @version Raffle.php v0.0 2017/10/12 新建
 */
class RaffleController extends BaseController {

	private $_model = null; //抽奖结果模型
	//private $_redis = null; //redis服务器实例

	/**
	 * 初始化方法 控制器被调用的时候先调用初始化方法
	 */
	public function init() {
	
		//调用父类的初始化方法
		parent::init();

		//实例化RaffleModel
		$this->_model = new RaffleModel();	
		
	}

	/**
	 * 输入中奖人数进行抽奖
	 */
	public function addRaffleAction() {

		//参数设置
		$data = [];
		$data['act_id'] = Common_Util::getHttpReqQuery($this,'act_id','Int','n',''); //活动ID
		$data['lucky_num'] = Common_Util::getHttpReqQuery($this,'lucky_num','Int','n',''); //中奖人数
		$level_num = $this->_model->raffleNum($data['act_id']);

		//一次最多抽奖抽六个，如果抽奖人数大于6的话返回状态码20001
		if ($data['lucky_num'] < 1 || $data['lucky_num'] > 6) {
			return Common_Util::returnJson('10007','抽奖人数只能为0~6之间的数字');
		}		

		//判断活动是否开启，没开启的话不允许发送弹幕
		$activity_model = new ActivityModel();
		$manager = $activity_model->getActManagerById($data['act_id']);
		if($manager['status'] != 1) {
			return Common_Util::returnJson('10008','活动未开启，不能抽奖');
		}

		//判断屏幕是否在线，不在线的话直接返回，不让其发弹幕
        $roomnum_data = $activity_model->getRoomnumById($data['act_id']);//根据活动id获取房间编号
        $screen_status = $this->_redis->get($roomnum_data['roomnum']);//获取房间号对应的屏幕是否在线
        if ((int)$screen_status != 9) {
            return Common_Util::returnJson('10009','屏幕不在线，无法抽奖');
        }

		
		//维护抽奖次数，如果表中无记录则置为1
		$data['level_num'] = empty($level_num) ? 1 : $level_num[0]['level_num'] + 1;
		$user_ids = $this->_model->checkRaffle($data);
		if (count($user_ids) < $data['lucky_num'] || $data['lucky_num'] <= 0) { //发送弹幕人数小于中奖人数，则退出
			return Common_Util::returnJson('10010','抽奖人数大于参与活动人数');
		}
		//满足要求调用抽奖方法，进行抽奖
		$data['user_ids'] = $user_ids;
		$result = $this->createRaffleAction($data);

	}

	/**
	 * 抽奖方法
	 *
	 * @param	Array	$data	抽奖所需要的信息(活动ID，中奖人数)
	 * @return	Array	$ret	中奖人的信息
	 */
	public function createRaffleAction($data) {
		
		//打乱用户ID，截取前中奖人数位作为幸运观众
    	shuffle($data['user_ids']);
		$lucky_user_ids = array_slice($data['user_ids'],0,$data['lucky_num'],true);
		
		//设置一个参数用于保存结果
		$result = 0;
		foreach ($lucky_user_ids as $lucky_user_id) {
			//向中奖表中插入中奖数据
			$raf = [];
			$raf['act_id'] = $data['act_id'];
			$raf['user_id'] = $lucky_user_id['user_id'];
			$raf['level_num'] = $data['level_num'];
			$raf['create_time'] = time();
			$result += $this->_model->addRaffle($raf);
		}

		if (is_numeric($result) && $result > 0) {
			$rafflelists = $this->_model->showRaffle($data['act_id']);//查询本次活动所有中奖人的信息
			$level_nums = array_unique(array_column($rafflelists, 'level_num'));//取出抽奖level_num序号
			$pos = array_search(max($level_nums), $level_nums);//取出最大的level_num 本次中奖的标识
			$last_level = $level_nums[$pos];//最后一次抽奖的level值
			$raffle_users = [];//中奖人信息数组

			foreach ($rafflelists as $rafflelist) {
				if($last_level == $rafflelist['level_num']) {
					if (!isset($raffle_users[$last_level]['level_num'])) {
						//判断level_num字段是否存在，不存在给其赋值，存在则跳过,避免每次循环申请内存为其赋值
						$raffle_users[$last_level]['level_num'] = $last_level;
					}
					$raffle_users[$last_level]['item'][] = $rafflelist;
					//unset($ret);
				}
			}

			//返回给小程序端
			Common_Util::returnJson('20004','抽奖成功',array_values($raffle_users));

			//以下逻辑是将中奖数据返回给PC端做展示用
			$activity_model = new ActivityModel();
			$manager = $activity_model->getActManagerById($data['act_id']);//根据活动id获取弹幕墙编号，将中奖信息发送到指定弹幕墙
			$raffle_users[$last_level]['user_portrait'] = $this->_model->showUsersPortraitForPC($data['act_id']);
			$raffle_users[$last_level]['lucky_num'] = count($raffle_users[$last_level]['item']);
			$raffle_users[$last_level]['users'] = count($raffle_users[$last_level]['user_portrait']);
			$result = array_values($raffle_users);
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$manager['roomnum'].'&message=RAFFLE%3A'.json_encode($result,JSON_UNESCAPED_UNICODE);
			$html = Common_Util::RequestHttpArray('get',$url);		
			//echo json_encode($result);
			exit();
		}

		return Common_Util::returnJson('20005','数据写入失败');
		exit();

	}

	/**
	 * 查询中奖信息
	 */
	public function showRaffleAction() {

		//参数过滤
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');	//活动ID
		
		$ret = $this->_model->showRaffle($act_id);
		if (empty($ret)) {
			return Common_Util::returnJson('20002','暂无此活动对应的中奖信息');
		}		
		return Common_Util::returnJson('20001','中奖信息返回成功',$ret);	

	}

	/**
	 * 根据抽奖次数返回分组后的中奖用户
	 */
	public function showRaffleByLevelAction() {

		//参数过滤
		$act_id = Common_Util::getHttpReqQuery($this,'act_id','Int','n','');	//活动ID	
		$rets = $this->_model->showRaffle($act_id);//根据活动ID查看此活动对应的中奖信息
		if (empty($rets)) {
			return Common_Util::returnJson('20002','暂无此活动对应的中奖信息');
		}
		$level_nums = array_unique(array_column($rets, 'level_num'));//取出抽奖level_num序号
		$data = [];
		foreach ($level_nums as $level_num) {
			foreach ($rets as $ret) {
				if($level_num == $ret['level_num']) {
					if (!isset($data[$level_num]['level_num'])) {//判断level_num字段是否存在，不存在给其赋值，存在则跳过
						$data[$level_num]['level_num'] = $level_num;
					}
					$data[$level_num]['item'][] = $ret;
					//unset($ret);
				}
			}
		}
		return Common_Util::returnJson('20001','中奖信息返回成功',array_values($data));
	}

	/**
	 * 给弹幕墙发送指令，关闭弹幕墙抽奖，准备下次抽奖
	 */

	public function clearRaffleScreenAction() {

		//参数过滤
		$roomnum = Common_Util::getHttpReqQuery($this,'roomnum','Int','n');//活动房间号
		$activity_model = new ActivityModel();
		$result = $activity_model->getActByRoomnum($roomnum);
		if(!empty($result)){
			$url = '127.0.0.1:4237/?cmd=send_to_group&group_id='.$roomnum.'&message=RAFFLE%3A'."清空屏幕准备下次抽奖";
			$html = Common_Util::RequestHttpArray('get',$url);
			echo "OK";
			exit;
		}
		return Common_Util::returnJson('10004','操作失败，请重试');

	}
}
