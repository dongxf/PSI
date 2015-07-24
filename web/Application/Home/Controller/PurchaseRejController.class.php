<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;

/**
 * 采购退货出库
 * @author 李静波
 *
 */
class PurchaseRejController extends Controller {

	public function index() {
		$us = new UserService();
		
		$this->assign("title", "采购退货出库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::PURCHASE_REJECTION)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function prbillList() {
		if (IS_POST) {
			return array("dataList" => array(), "totalCount" => 0);
		}
	}
}
