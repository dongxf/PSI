<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\BizConfigService;
use Home\Service\UserService;

/**
 * PSI Base Controller
 *
 * @author 李静波
 *        
 */
class PSIBaseController extends Controller {

	protected function initVar() {
		$bcs = new BizConfigService();
		$this->assign("productionName", $bcs->getProductionName());
		
		$this->assign("uri", __ROOT__ . "/");
		
		$us = new UserService();
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
	}
	
	/**
	 * 跳转到登录页面
	 */
	protected function gotoLoginPage() {
		redirect(__ROOT__ . "/Home/User/login");
	}
}