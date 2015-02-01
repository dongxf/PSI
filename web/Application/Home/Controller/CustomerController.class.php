<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\CustomerService;
use Home\Common\FIdConst;

class CustomerController extends Controller {

	public function index() {
		$us = new UserService();

		$this->assign("title", "客户资料");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoginUserName());
		$this->assign("dtFlag", getdate()[0]);

		if ($us->hasPermission(FIdConst::CUSTOMER)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function categoryList() {
		if (IS_POST) {
			$this->ajaxReturn((new CustomerService())->categoryList());
		}
	}

	public function editCategory() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
				"code" => I("post.code"),
				"name" => I("post.name")
			);
			$this->ajaxReturn((new CustomerService())->editCategory($params));
		}
	}

	public function deleteCategory() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$this->ajaxReturn((new CustomerService())->deleteCategory($params));
		}
	}

	public function editCustomer() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
				"code" => I("post.code"),
				"name" => I("post.name"),
				"contact01" => I("post.contact01"),
				"mobile01" => I("post.mobile01"),
				"tel01" => I("post.tel01"),
				"qq01" => I("post.qq01"),
				"contact02" => I("post.contact02"),
				"mobile02" => I("post.mobile02"),
				"tel02" => I("post.tel02"),
				"qq02" => I("post.qq02"),
				"categoryId" => I("post.categoryId"),
				"initReceivables" =>I("post.initReceivables"),
				"initReceivablesDT" => I("post.initReceivablesDT")
			);
			$this->ajaxReturn((new CustomerService())->editCustomer($params));
		}
	}

	public function customerList() {
		if (IS_POST) {
			$params = array(
				"categoryId" => I("post.categoryId"),
				"page" => I("post.page"),
				"start" => I("post.start"),
				"limit" => I("post.limit")
			);
			$this->ajaxReturn((new CustomerService())->customerList($params));
		}
	}

	public function deleteCustomer() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$this->ajaxReturn((new CustomerService())->deleteCustomer($params));
		}
	}

	public function queryData() {
		if (IS_POST) {
			$params = array(
				"queryKey" => I("post.queryKey"),
			);
			$this->ajaxReturn((new CustomerService())->queryData($params));
		}
	}
}
