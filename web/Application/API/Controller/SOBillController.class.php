<?php

namespace API\Controller;

use API\Service\SOBillApiService;
use Think\Controller;

class SOBillController extends Controller {

	public function sobillList() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"page" => I("post.page"),
					"limit" => 10,
					"billStatus" => - 1
			];
			
			$service = new SOBillApiService();
			
			$this->ajaxReturn($service->sobillList($params));
		}
	}

	/**
	 * 某个采购订单的详情
	 */
	public function sobillInfo() {
		if (IS_POST) {
			$params = [
					"tokenId" => I("post.tokenId"),
					"id" => I("post.id")
			];
			
			$service = new SOBillApiService();
			
			$this->ajaxReturn($service->sobillInfo($params));
		}
	}
}