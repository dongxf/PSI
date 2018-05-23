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
}