<?php

namespace API\DAO;

use Home\DAO\PSIBaseExDAO;

/**
 * 客户API DAO
 *
 * @author 李静波
 */
class CustomerApiDAO extends PSIBaseExDAO {

	// TODO 数据域
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

	// TODO 数据域
	public function categoryList($params) {
		$db = $this->db;
		
		$result = [];
		
		$result[] = [
				"id" => "-1",
				"name" => "[所有分类]"
		];
		
		$sql = "select id, name
				from t_customer_category
				order by code";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}
}