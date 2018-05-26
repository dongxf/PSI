<?php

namespace API\DAO;

use Home\DAO\PSIBaseExDAO;

/**
 * 客户API DAO
 *
 * @author 李静波
 */
class CustomerApiDAO extends PSIBaseExDAO {

	public function customerList($params) {
		$db = $this->db;
		
		$page = $params["page"];
		if (! $page) {
			$page = 1;
		}
		$limit = $params["limit"];
		if (! $limit) {
			$limit = 10;
		}
		
		$start = ($page - 1) * $limit;
		
		$result = [];
		
		$sql = "select code, name
				from t_customer
				order by code
				limit %d, %d";
		$data = $db->query($sql, $start, $limit);
		foreach ( $data as $v ) {
			$result[] = [
					"code" => $v["code"],
					"name" => $v["name"]
			];
		}
		
		$sql = "select count(*) as cnt
				from t_customer";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		$totalPage = ceil($cnt / $limit);
		
		return [
				"dataList" => $result,
				"totalPage" => $totalPage
		];
	}
}