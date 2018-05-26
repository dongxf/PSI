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
					"limit" => 10
			];
			
			$service = new CustomerApiService();
			
			$this->ajaxReturn($service->customerList($params));
		}
	}
}