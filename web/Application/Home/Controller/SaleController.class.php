<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\WSBillService;
use Home\Service\SRBillService;

/**
 * 销售Controller
 * @author 李静波
 *
 */
class SaleController extends Controller {

	public function wsIndex() {
		$us = new UserService();
		
		$this->assign("title", "销售出库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function wsBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsBillInfo($params));
		}
	}

	public function editWSBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->editWSBill($params));
		}
	}

	public function wsbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"customerId" => I("post.customerId"),
					"receivingType" => I("post.receivingType"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsbillList($params));
		}
	}

	public function wsBillDetailList() {
		if (IS_POST) {
			$params = array(
					"billId" => I("post.billId")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->wsBillDetailList($params));
		}
	}

	public function deleteWSBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->deleteWSBill($params));
		}
	}

	public function commitWSBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$ws = new WSBillService();
			$this->ajaxReturn($ws->commitWSBill($params));
		}
	}

	public function srIndex() {
		$us = new UserService();
		
		$this->assign("title", "销售退货入库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::SALE_REJECTION)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function srbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"warehouseId" => I("post.warehouseId"),
					"customerId" => I("post.customerId"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$sr = new SRBillService();
			$this->ajaxReturn($sr->srbillList($params));
		}
	}

	public function srBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.billId")
			);
			
			$sr = new SRBillService();
			$this->ajaxReturn($sr->srBillDetailList($params));
		}
	}

	public function srBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->srBillInfo($params));
		}
	}

	public function selectWSBillList() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref"),
					"customerId" => I("post.customerId"),
					"warehouseId" => I("post.warehouseId"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"sn" => I("post.sn"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->selectWSBillList($params));
		}
	}

	/**
	 * 新增或者编辑销售退货入库单
	 */
	public function editSRBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->editSRBill($params));
		}
	}

	public function getWSBillInfoForSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->getWSBillInfoForSRBill($params));
		}
	}

	/**
	 * 删除销售退货入库单
	 */
	public function deleteSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->deleteSRBill($params));
		}
	}

	/**
	 * 提交销售退货入库单
	 */
	public function commitSRBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$rs = new SRBillService();
			$this->ajaxReturn($rs->commitSRBill($params));
		}
	}
	
	/**
	 * 生成pdf文件
	 */
	public function pdf() {
		$params = array(
				"ref" => I("get.ref")
		);
		
		$ws = new WSBillService();
		$ws->pdf($params);
	}
}
