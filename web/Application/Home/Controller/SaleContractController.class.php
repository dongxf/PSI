<?php

namespace Home\Controller;

use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\SCBillService;

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

	/**
	 * 销售合同主表列表
	 */
	public function scbillList() {
		if (IS_POST) {
			$params = [
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"fromDT" => I("post.fromDT"),
					"toDT" => I("post.toDT"),
					"customerId" => I("post.customerId"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			];
			
			$service = new SCBillService();
			$this->ajaxReturn($service->scbillList($params));
		}
	}

	/**
	 * 销售合同详情
	 */
	public function scBillInfo() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$service = new SCBillService();
			$this->ajaxReturn($service->scBillInfo($params));
		}
	}

	/**
	 * 新增或编辑销售合同
	 */
	public function editSCBill() {
		if (IS_POST) {
			$json = I("post.jsonStr");
			$ps = new SCBillService();
			$this->ajaxReturn($ps->editSCBill($json));
		}
	}
}