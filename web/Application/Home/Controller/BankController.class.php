<?php

namespace Home\Controller;

use Home\Common\FIdConst;
use Home\Service\UserService;
use Home\Service\BankService;

/**
 * 银行账户Controller
 *
 * @author 李静波
 *        
 */
class BankController extends PSIBaseController {

	/**
	 * 银行账户 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GL_BANK_ACCOUNT)) {
			$this->initVar();
			
			$this->assign("title", "银行账户");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/Bank/index");
		}
	}
	
	/**
	 * 返回所有的公司列表
	 */
	public function companyList() {
		if (IS_POST) {
			$service = new BankService();
			$this->ajaxReturn($service->companyList());
		}
	}
	
}