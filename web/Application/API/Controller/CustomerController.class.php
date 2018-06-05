<?php

namespace API\Controller;

use Think\Controller;
use API\Service\CustomerApiService;

class CustomerController extends Controller {

	/**
	 * 查询客户资料列表
	 */
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

	/**
	 * 获得客户分类列表，包括[全部]分类这个数据里面没有的记录，用于查询条件界面里面的客户分类字段
	 */
	public function categoryListWithAllCategory() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->categoryListWithAllCategory($params));
		}
	}

	/**
	 * 获得客户分类列表
	 */
	public function categoryList() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->categoryList($params));
		}
	}

	/**
	 * 新增或编辑某个客户分类
	 */
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

	/**
	 * 删除某个客户分类
	 */
	public function deleteCategory() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"id" => I("post.categoryId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->deleteCategory($params));
		}
	}

	/**
	 * 获得某个客户的详细信息
	 */
	public function customerInfo() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"id" => I("post.categoryId")
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->customerInfo($params));
		}
	}
}