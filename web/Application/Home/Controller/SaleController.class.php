<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;
use Home\Common\FIdConst;
use Home\Service\WSBillService;

class SaleController extends Controller {
    public function wsIndex(){
		$us = new UserService();
		
		$this->assign("title", "销售出库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoginUserName());
		$this->assign("dtFlag", getdate()[0]);

		if ($us->hasPermission(FIdConst::WAREHOUSING_SALE)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
	
	public function wsBillInfo() {
		$params = array (
			"id" => I("post.id")
		);
		
		$this->ajaxReturn((new WSBillService())->wsBillInfo($params));
	}
	
	
	public function editWSBill() {
		$params = array (
			"jsonStr" => I("post.jsonStr")
		);
		
		$this->ajaxReturn((new WSBillService())->editWSBill($params));
	}
	
	public function wsbillList() {
		$params = array (
		);
		
		$this->ajaxReturn((new WSBillService())->wsbillList($params));
	}
	
	public function wsBillDetailList() {
		$params = array (
			"billId" => I("post.billId")
		);
		
		$this->ajaxReturn((new WSBillService())->wsBillDetailList($params));
	}
	
	public function deleteWSBill() {
		$params = array (
			"id" => I("post.id")
		);
		
		$this->ajaxReturn((new WSBillService())->deleteWSBill($params));
	}
	
	public function commitWSBill() {
		$params = array (
			"id" => I("post.id")
		);
		
		$this->ajaxReturn((new WSBillService())->commitWSBill($params));
	}
}