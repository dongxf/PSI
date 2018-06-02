<?php

namespace API\Controller;

use Think\Controller;
use API\Service\CustomerApiService;

class CustomerController extends Controller {

	public function customerList() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"page" => I("post.page"),
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"address" => I("post.address"),
					"contact" => I("post.contact"),
					"mobile" => I("post.mobile"),
					"tel" => I("post.tel"),
					"qq" => I("post.qq"),
					"limit" => 10
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->customerList($params));
		}
	}

	public function categoryListWithAllCategory() {
		$params = [
				"tokenId" => I("post.tokenId")
		];
		
		$service = new CustomerApiService();
		
		$this->ajaxReturn($service->categoryListWithAllCategory($params));
	}

	public function categoryList() {
		$params = [
				"tokenId" => I("post.tokenId")
		];
		
		$service = new CustomerApiService();
		
		$this->ajaxReturn($service->categoryList($params));
	}
}