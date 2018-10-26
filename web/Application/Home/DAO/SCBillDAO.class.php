<?php

namespace Home\DAO;

/**
 * 销售合同 DAO
 *
 * @author 李静波
 */
class SCBillDAO extends PSIBaseExDAO {

	/**
	 * 获得销售合同主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function scbillList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$customerId = $params["customerId"];
		
		$queryParams = [];
		
		$result = [];
		$cnt = 0;
		
		return [
				"dataList" => $result,
				"totalCount" => $cnt
		];
	}
}