<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\BizConfigService;
use Home\Common\FIdConst;
use Home\Service\BillViewService;

/**
 * 查看单据Controller
 *
 * @author 李静波
 *        
 */
class BillController extends Controller {

	/**
	 * 查看单据页面
	 */
	public function index() {
		$fid = I("get.fid");
		$refType = I("get.refType");
		$ref = I("get.ref");
		
		$this->assign("fid", $fid);
		$this->assign("refType", $refType);
		$this->assign("ref", $ref);
		
		$this->assign("title", "查看单据");
		$this->assign("uri", __ROOT__ . "/");
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		$us = new UserService();
		
		$pm = "0";
		if ($fid == FIdConst::INVENTORY_QUERY || $fid == FIdConst::PAYABLES || $fid == FIdConst::RECEIVING) {
			$pm = $us->hasPermission($fid) ? "1" : "0";
		}
		$this->assign("pm", $pm);
		
		$this->display();
	}

	/**
	 * 采购入库单
	 */
	public function pwBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->pwBillInfo($params));
		}
	}

	/**
	 * 采购退货出库单
	 */
	public function prBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->prBillInfo($params));
		}
	}

	/**
	 * 销售出库单
	 */
	public function wsBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->wsBillInfo($params));
		}
	}

	/**
	 * 销售退货入库单
	 */
	public function srBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->srBillInfo($params));
		}
	}

	/**
	 * 调拨单
	 */
	public function itBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->itBillInfo($params));
		}
	}

	/**
	 * 盘点单
	 */
	public function icBillInfo() {
		if (IS_POST) {
			$params = array(
					"ref" => I("post.ref")
			);
			
			$bs = new BillViewService();
			$this->ajaxReturn($bs->icBillInfo($params));
		}
	}
}