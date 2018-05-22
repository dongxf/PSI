<?php

namespace H5\Service;

use Home\Service\SOBillService;
use H5\DAO\SOBillDAOH5;

/**
 * 用户Service for H5
 *
 * @author 李静波
 */
class SOBillServiceH5 extends SOBillService {

	private function billStatusCodeToName($code) {
		switch ($code) {
			case 0 :
				return "待审核";
			case 1000 :
				return "已审核";
			case 1001 :
				return "订单取消";
			case 2000 :
				return "部分出库";
			case 2001 :
				return "部分出库-订单关闭";
			case 3000 :
				return "全部出库";
			case 3001 :
				return "全部出库-订单关闭";
			default :
				return "";
		}
	}

	private function receivingTypeCodeToName($code) {
		switch ($code) {
			case 0 :
				return "记应收账款";
			case 1 :
				return "现金收款";
			default :
				return "";
		}
	}

	public function sobillListForH5($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$data = $this->sobillList($params);
		
		$result = [];
		
		foreach ( $data["dataList"] as $v ) {
			$result[] = [
					"id" => $v["id"],
					"ref" => $v["ref"],
					"dealDate" => $v["dealDate"],
					"customerName" => $v["customerName"],
					"goodsMoney" => $v["goodsMoney"],
					"billStatus" => $this->billStatusCodeToName($v["billStatus"]),
					"receivingType" => $this->receivingTypeCodeToName($v["receivingType"])
			];
		}
		
		return $result;
	}

	public function queryCustomerData($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SOBillDAOH5($this->db());
		return $dao->queryCustomerData($params);
	}
}