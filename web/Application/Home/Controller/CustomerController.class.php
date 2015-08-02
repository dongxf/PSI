<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\CustomerService;
use Home\Common\FIdConst;

/**
 * 客户资料Controller
 * @author 李静波
 *
 */
class CustomerController extends Controller {

	public function index() {
		$us = new UserService();
		
		$this->assign("title", "客户资料");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
		
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);
		
		if ($us->hasPermission(FIdConst::CUSTOMER)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function categoryList() {
		if (IS_POST) {
			$cs = new CustomerService();
			$params = array(
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"contact" => I("post.contact"),
					"mobile" => I("post.mobile"),
					"tel" => I("post.tel"),
					"qq" => I("post.qq")
			);
				
			$this->ajaxReturn($cs->categoryList($params));
		}
	}

	public function editCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->editCategory($params));
		}
	}

	public function deleteCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->deleteCategory($params));
		}
	}

	public function editCustomer() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"addressReceipt" => I("post.addressReceipt"),
					"contact01" => I("post.contact01"),
					"mobile01" => I("post.mobile01"),
					"tel01" => I("post.tel01"),
					"qq01" => I("post.qq01"),
					"contact02" => I("post.contact02"),
					"mobile02" => I("post.mobile02"),
					"tel02" => I("post.tel02"),
					"qq02" => I("post.qq02"),
					"categoryId" => I("post.categoryId"),
					"initReceivables" => I("post.initReceivables"),
					"initReceivablesDT" => I("post.initReceivablesDT")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->editCustomer($params));
		}
	}

	public function customerList() {
		if (IS_POST) {
			$params = array(
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"contact" => I("post.contact"),
					"mobile" => I("post.mobile"),
					"tel" => I("post.tel"),
					"qq" => I("post.qq"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->customerList($params));
		}
	}

	public function deleteCustomer() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->deleteCustomer($params));
		}
	}

	public function queryData() {
		if (IS_POST) {
			$params = array(
					"queryKey" => I("post.queryKey")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->queryData($params));
		}
	}
	
	public function customerInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$cs = new CustomerService();
			$this->ajaxReturn($cs->customerInfo($params));
		}
	}
}
