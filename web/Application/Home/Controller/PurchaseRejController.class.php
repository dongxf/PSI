<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;
use Home\Service\PRBillService;

/**
 * 采购退货出库Controller
 *
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
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"supplierId" => I("post.supplierId"),
					"receivingType" => I("post.receivingType"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$pr = new PRBillService();
			$this->ajaxReturn($pr->prbillList($params));
		}
	}

	public function prBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->prBillInfo($params));
		}
	}

	public function editPRBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->editPRBill($params));
		}
	}

	public function selectPWBillList() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref"),
					"supplierId" => I("post.supplierId"),
					"warehouseId" => I("post.warehouseId"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->selectPWBillList($params));
		}
	}

	public function getPWBillInfoForPRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->getPWBillInfoForPRBill($params));
		}
	}

	public function prBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->prBillDetailList($params));
		}
	}

	public function deletePRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->deletePRBill($params));
		}
	}

	public function commitPRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$pr = new PRBillService();
			
			$this->ajaxReturn($pr->commitPRBill($params));
		}
	}
}
