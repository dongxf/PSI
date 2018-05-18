<?php

namespace H5\Controller;

use Think\Controller;
use H5\Service\SOBillServiceH5;

class SaleController extends Controller {

	public function sobillList() {
		if (IS_POST) {
			$params = [
					"start" => 0,
					"limit" => 20,
					"billStatus" => - 1
			];
			
			$ss = new SOBillServiceH5();
			
			$this->ajaxReturn($ss->sobillList($params));
		}
	}
}