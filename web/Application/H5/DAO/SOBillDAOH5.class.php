<?php

namespace H5\DAO;

use Home\DAO\SOBillDAO;

/**
 * 用户 DAO - H5
 *
 * @author 李静波
 */
class SOBillDAOH5 extends SOBillDAO {

	public function queryCustomerData($params) {
		$db = $this->db;
		
		$query = $params["query"];
		
		$sql = "select id, name
				from t_customer
				where code like '%s' or name like '%s' or py like '%s' 
				order by code
				limit 20";
		$queryParams = [];
		$queryParams[] = "%$query%";
		$queryParams[] = "%$query%";
		$queryParams[] = "%$query%";
		
		$data = $db->query($sql, $queryParams);
		
		$result = [];
		
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}
}