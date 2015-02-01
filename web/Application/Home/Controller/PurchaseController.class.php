<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;
use Home\Service\PWBillService;
use Home\Common\FIdConst;

class PurchaseController extends Controller {
    public function pwbillIndex(){
		$us = new UserService();
		
		$this->assign("title", "采购入库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoginUserName());
		$this->assign("dtFlag", getdate()[0]);

		if ($us->hasPermission(FIdConst::PURCHASE_WAREHOUSE)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
	
	public function pwbillList() {
		if (IS_POST) {
			$this->ajaxReturn((new PWBillService())->pwbillList());
		}
	}
	
	public function pwBillDetailList() {
		if (IS_POST) {
			$pwbillId = I("post.pwBillId");
			$this->ajaxReturn((new PWBillService())->pwBillDetailList($pwbillId));
		}
	}
	
	public function editPWBill() {
		if (IS_POST) {
			$json = I("post.jsonStr");
			$this->ajaxReturn((new PWBillService())->editPWBill($json));
		}
	}
	
	public function pwBillInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$this->ajaxReturn((new PWBillService())->pwBillInfo($id));
		}
	}
	
	public function deletePWBill() {
		if (IS_POST) {
			$id = I("post.id");
			$this->ajaxReturn((new PWBillService())->deletePWBill($id));
		}
	}
	
	public function commitPWBill() {
		if (IS_POST) {
			$id = I("post.id");
			$this->ajaxReturn((new PWBillService())->commitPWBill($id));
		}
	}
}