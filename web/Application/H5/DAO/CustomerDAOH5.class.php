<?php

namespace H5\DAO;

use Home\DAO\CustomerDAO;

/**
 * 用户 DAO - H5
 *
 * @author 李静波
 */
class CustomerDAOH5 extends CustomerDAO {

	public function queryCustomerCategoryH5($params) {
		$db = $this->db;
		
		$query = $params["query"];
		
		$queryParams = [];
		if ($query == "*" || $query == "?") {
			// 所有分类
			$sql = "select id, name
					from t_customer_category
					order by code";
		} else {
			$sql = "select id, name
				from t_customer_category
				where code like '%s' or name like '%s'
				order by code ";
			$queryParams[] = "%$query%";
			$queryParams[] = "%$query%";
		}
		
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