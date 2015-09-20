<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;
use Home\Service\ITBillService;
use Home\Service\BizConfigService;

/**
 * 库间调拨
 *
 * @author 李静波
 *        
 */
class InvTransferController extends Controller {

	/**
	 * 库间调拨 - 首页
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::INVENTORY_TRANSFER)) {
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
			$this->assign("title", "库间调拨");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 调拨单主表信息列表
	 */
	public function itbillList() {
		if (IS_POST) {
			$params = array(
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"fromWarehouseId" => I("post.fromWarehouseId"),
					"toWarehouseId" => I("post.toWarehouseId"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->itbillList($params));
		}
	}

	/**
	 * 新建或编辑调拨单
	 */
	public function editITBill() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->editITBill($params));
		}
	}

	/**
	 * 获取单个调拨单的信息
	 */
	public function itBillInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->itBillInfo($params));
		}
	}

	/**
	 * 调拨单明细信息
	 */
	public function itBillDetailList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->itBillDetailList($params));
		}
	}

	/**
	 * 删除调拨单
	 */
	public function deleteITBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->deleteITBill($params));
		}
	}

	/**
	 * 提交调拨单
	 */
	public function commitITBill() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			
			$is = new ITBillService();
			
			$this->ajaxReturn($is->commitITBill($params));
		}
	}
}
