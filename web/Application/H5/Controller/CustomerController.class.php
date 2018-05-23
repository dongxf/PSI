<?php

namespace H5\Controller;

use H5\Service\CustomerServiceH5;
use Think\Controller;

class CustomerController extends Controller {

	public function queryCustomerCategory() {
		if (IS_POST) {
			$params = [
					"query" => I("post.query")
			];
			
			$ss = new CustomerServiceH5();
			
			$data = $ss->queryCustomerCategoryH5($params);
			
			$this->ajaxReturn($data);
		}
	}

	public function customerList() {
		if (IS_POST) {
			$page = I("post.page");
			
			if (! $page) {
				$page = 1;
			}
			
			$params = [
					"page" => $page,
					"start" => ($page - 1) * 10,
					"limit" => 10,
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"contact" => I("post.contact"),
					"mobile" => I("post.mobile"),
					"tel" => I("post.tel"),
					"qq" => I("post.qq")
			];
			
			$cs = new CustomerServiceH5();
			
			$this->ajaxReturn($cs->customerListForH5($params));
		}
	}

	public function customerDetail() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$cs = new CustomerServiceH5();
			
			$this->ajaxReturn($cs->customerDetail($params));
		}
	}
}