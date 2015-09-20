<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;

/**
 * 首页Controller
 * 
 * @author 李静波
 *        
 */
class IndexController extends Controller {

	/**
	 * 首页
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission()) {
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			$this->assign("title", "首页");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			$this->assign("pSale", $us->hasPermission(FIdConst::PORTAL_SALE) ? 1: 0);
			$this->assign("pInventory", $us->hasPermission(FIdConst::PORTAL_INVENTORY) ? 1: 0);
			$this->assign("pPurchase", $us->hasPermission(FIdConst::PORTAL_PURCHASE) ? 1: 0);
			$this->assign("pMoney", $us->hasPermission(FIdConst::PORTAL_MONEY) ? 1: 0);
				
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}
}