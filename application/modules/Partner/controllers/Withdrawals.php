<?php

/**
 *
 * @name WithdrawalsController
 * @desc 建议控制器
 * @author Leslie
 * @link mailto:lesliedream@outlook.com 
 * @version Advise.php v0.0 2018/3/8 新建
 */
class WithdrawalsController extends BaseController {

	private $_model = null;
	private $_userinfo = [];

	/**
	 * 初始化方法
	 */
	public function init() {

		//调用父类的初始化方法
		parent::init();

		//实例化建议类模型
		$this->_model = new PtnWithdrawalsModel();

		//根据sessionID获取用户ID
		$this->_userinfo = $this->verifySessionid($this);

	}

	/**
	 * 检测用户账户金额方法
	 */
	public function getAccountAction() {

		$account = $this->_model->getAccount($this->_userinfo['user_id']);
		if (empty($account)) {
			return Common_Util::returnJson('20002','暂无数据');
		}
		return Common_Util::returnJson('20001','查询成功',$account);		

	}

	/**
	 * 提现前判断用户是否已经实名认证
	 */
	public function isAuthenticationAction() {

		$user_model = new PtnUserModel();
		$userinfo = $user_model->getUserInfo($this->_userinfo['user_id']);
		switch ($userinfo['verifystatus'] ) {
			case 0:
			case 1:
			case 3:
				return Common_Util::returnJson('20002','请先去认证身份');
			case 2:
				return Common_Util::returnJson('20001','认证成功');
		}

	}

	/**
	 * 用户提现方法
	 */
	public function withdrawalsAction() {

		$money = Common_Util::getHttpReqQuery($this,'money','Str','n');
		$money = sprintf("%.2f", floatval ($money));
		if ($money <= 0) {
			return Common_Util::returnJson('10004','提现金额必须大于0');
		}
		$account_money = $this->_model->getAccount($this->_userinfo['user_id'])['sum_money'];
		if ($money > $account_money) {
			return Common_Util::returnJson('10004','提现金额不能大于账户余额');
		}

		//从用户表中查询用户信息 1.检测身份是否认证 2.检测提现额度是否大于最低额度
		$user_model = new PtnUserModel();
		$userinfo = $user_model->getUserInfo($this->_userinfo['user_id']);

		//身份认证未通过，不能提现
		if ($userinfo['verifystatus'] !== 2 || ($userinfo['forbidden_endtime'] > time() && $userinfo['isforbidden'] === 1 ) || $userinfo['isdel'] === 1 || $userinfo['isforbidden'] == 2) {
			return Common_Util::returnJson('10008','身份认证未通过，不能提现');
		}
		if ($money < $userinfo['least_money_limit']) {
			return Common_Util::returnJson('10009','提现额度不能小于'.$userinfo['least_money_limit']);
		}

		$res = $this->_model-> withdrawals($this->_userinfo['user_id'],$money,$account_money);	
		if ($res) {
			return Common_Util::returnJson('20001','提现申请已经提交，请等待管理员审核');
		}
		return Common_Util::retutnJson('20002','提交申请失败，请重试');

	}

}//class
