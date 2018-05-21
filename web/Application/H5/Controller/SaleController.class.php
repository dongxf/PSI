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
					"billStatus" => I("post.billStatus"),
					"ref" => I("post.ref"),
					"receivingType" => I("post.receivingType")
			];
			
			$ss = new SOBillServiceH5();
			
			$this->ajaxReturn($ss->sobillListForH5($params));
		}
	}

	public function sobillDetail() {
		if (IS_POST) {
			$params = [
					"id" => I("post.id")
			];
			
			$ss = new SOBillServiceH5();
			
			$data = $ss->soBillInfo($params);
			
			$this->ajaxReturn($data);
		}
	}
}