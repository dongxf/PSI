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
		
		$result = [];
		
		$sql = "select code, name
				from t_customer
				order by code";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$result[] = [
					"code" => $v["code"],
					"name" => $v["name"]
			];
		}
		
		return $result;
	}
}