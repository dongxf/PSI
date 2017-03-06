<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 应付账款 DAO
 *
 * @author 李静波
 */
class PayablesDAO extends PSIBaseExDAO {

	/**
	 * 往来单位分类
	 */
	public function payCategoryList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$result = array();
		$result[0]["id"] = "";
		$result[0]["name"] = "[全部]";
		
		$id = $params["id"];
		if ($id == "supplier") {
			$sql = "select id, name from t_supplier_category ";
			$queryParams = array();
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "t_supplier_category", $loginUserId);
			if ($rs) {
				$sql .= " where " . $rs[0];
				$queryParams = $rs[1];
			}
			$sql .= " order by code";
			$data = $db->query($sql, $queryParams);
			foreach ( $data as $i => $v ) {
				$result[$i + 1]["id"] = $v["id"];
				$result[$i + 1]["name"] = $v["name"];
			}
		} else {
			$sql = "select id,  code, name from t_customer_category ";
			$queryParams = array();
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "t_customer_category", $loginUserId);
			if ($rs) {
				$sql .= " where " . $rs[0];
				$queryParams = $rs[1];
			}
			$sql .= " order by code";
			$data = $db->query($sql, $queryParams);
			foreach ( $data as $i => $v ) {
				$result[$i + 1]["id"] = $v["id"];
				$result[$i + 1]["name"] = $v["name"];
			}
		}
		
		return $result;
	}
}