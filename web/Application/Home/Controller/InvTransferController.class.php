<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;
use Home\Service\ITBillService;

/**
 * 库间调拨
 * @author 李静波
 *
 */
class InvTransferController extends Controller {

	public function index() {
		$us = new UserService();
		
		$this->assign("title", "库间调拨");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::INVENTORY_TRANSFER)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function itbillList() {
		if (IS_POST) {
			$params = array(
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
				
			$is = new ITBillService();
			
			return $this->ajaxReturn($is->itbillList($params));
		}
	}
}
