<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 应收账款 DAO
 *
 * @author 李静波
 */
class ReceivablesDAO extends PSIBaseExDAO {

	/**
	 * 往来单位分类
	 */
	public function rvCategoryList($params) {
		$db = $this->db;
		$result = array();
		$result[0]["id"] = "";
		$result[0]["name"] = "[全部]";
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		if ($id == "customer") {
			$sql = "select id, name from t_customer_category ";
			
			$queryParams = array();
			
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_customer_category");
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
			$sql = "select id, name from t_supplier_category ";
			$queryParams = array();
			
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_supplier_category");
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