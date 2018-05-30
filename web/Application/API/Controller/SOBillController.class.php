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
}