<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;
use Home\Service\PWBillService;
use Home\Common\FIdConst;

/**
 * 采购Controller
 * @author 李静波
 *
 */
class PurchaseController extends Controller {
    public function pwbillIndex(){
		$us = new UserService();
		
		$this->assign("title", "采购入库");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::PURCHASE_WAREHOUSE)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
	
	public function pwbillList() {
		if (IS_POST) {
			$ps = new PWBillService();
			$params = array(
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$this->ajaxReturn($ps->pwbillList($params));
		}
	}
	
	public function pwBillDetailList() {
		if (IS_POST) {
			$pwbillId = I("post.pwBillId");
			$ps = new PWBillService();
			$this->ajaxReturn($ps->pwBillDetailList($pwbillId));
		}
	}
	
	public function editPWBill() {
		if (IS_POST) {
			$json = I("post.jsonStr");
			$ps = new PWBillService();
			$this->ajaxReturn($ps->editPWBill($json));
		}
	}
	
	public function pwBillInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$ps = new PWBillService();
			$this->ajaxReturn($ps->pwBillInfo($id));
		}
	}
	
	public function deletePWBill() {
		if (IS_POST) {
			$id = I("post.id");
			$ps = new PWBillService();
			$this->ajaxReturn($ps->deletePWBill($id));
		}
	}
	
	public function commitPWBill() {
		if (IS_POST) {
			$id = I("post.id");
			$ps = new PWBillService();
			$this->ajaxReturn($ps->commitPWBill($id));
		}
	}
}