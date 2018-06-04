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
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->categoryListWithAllCategory($params));
		}
	}

	public function categoryList() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->categoryList($params));
		}
	}

	public function editCategory() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"psId" => I("post.psId"),
					"fromDevice" => I("post.fromDevice")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->editCategory($params));
		}
	}

	/**
	 * 获得所有的价格体系中的价格
	 */
	public function priceSystemList() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->priceSystemList($params));
		}
	}

	/**
	 * 获得某个客户分类的详细信息
	 */
	public function categoryInfo() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"categoryId" => I("post.categoryId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->categoryInfo($params));
		}
	}
}