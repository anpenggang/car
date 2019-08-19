<?php
class BannerController extends BaseController {
	private $_userinfo = [];
	/** 
     * 初始化方法
     */
    public function init() {
		
        //调用父类的初始化方法
        parent::init();

        //实例化审核模型
        $this->_model = new PtnBannerModel();

        //根据sessionID获取用户ID
        $this->_userinfo = $this->verifySessionid($this);

    }		
	
	/**
	 *获取banner详情
	 */
	public function BannerInfoAction(){
			
			$banner = $this->_model->getBannerInfo();
			if($banner){
				return Common_Util::returnJson('10006','成功',$banner);
			}else{
				return Common_Util::returnJson('10003','加载banner失败');
			}
	}
	
}
?>
