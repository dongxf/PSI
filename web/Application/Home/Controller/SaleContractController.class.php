<?php

namespace Home\Controller;

use Home\Service\UserService;
use Home\Common\FIdConst;

/**
 * 销售合同Controller
 *
 * @author 李静波
 *        
 */
class SaleContractController extends PSIBaseController {

	/**
	 * 销售合同 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::SALE_CONTRACT)) {
			$this->initVar();
			
			$this->assign("title", "销售合同");
			
			$this->display();
		} else {
			$this->gotoLoginPage("/Home/SaleContract/index");
		}
	}
}