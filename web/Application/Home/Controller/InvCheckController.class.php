<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;
use Home\Service\ICBillService;

/**
 * 库存盘点Controller
 * 
 * @author 李静波
 *        
 */
class InvCheckController extends Controller {

	public function index() {
		$us = new UserService();
		
		$this->assign("title", "库存盘点");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::INVENTORY_CHECK)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function icbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$ic = new ICBillService();
			$this->ajaxReturn($ic->icbillList($params));
		}
	}

	public function icBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ic = new ICBillService();
			
			$this->ajaxReturn($ic->icBillInfo($params));
		}
	}

	public function editICBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$ic = new ICBillService();
			
			$this->ajaxReturn($ic->editICBill($params));
		}
	}

	public function icBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ic = new ICBillService();
			
			$this->ajaxReturn($ic->icBillDetailList($params));
		}
	}

	public function deleteICBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ic = new ICBillService();
			
			$this->ajaxReturn($ic->deleteICBill($params));
		}
	}
	
	public function commitICBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
				
			$ic = new ICBillService();
				
			$this->ajaxReturn($ic->commitICBill($params));
		}
	}
}
