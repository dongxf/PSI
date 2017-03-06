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
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_customer_category", $loginUserId);
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
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "t_supplier_category", $loginUserId);
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

	/**
	 * 应收账款列表
	 */
	public function rvList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)){
			return $this->emptyResult();
		}
		
		$caType = $params["caType"];
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		if ($caType == "customer") {
			$queryParams = array();
			$sql = "select r.id, r.ca_id, c.code, c.name, r.act_money, r.balance_money, r.rv_money
					from t_receivables r, t_customer c
					where (r.ca_type = '%s' and r.ca_id = c.id)";
			$queryParams[] = $caType;
			
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$sql .= " order by c.code
					limit %d , %d ";
			$queryParams[] = $start;
			$queryParams[] = $limit;
			$data = $db->query($sql, $queryParams);
			$result = array();
			
			foreach ( $data as $i => $v ) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
				$result[$i]["rvMoney"] = $v["rv_money"];
			}
			
			$queryParams = array();
			$sql = "select count(*) as cnt
					from t_receivables r, t_customer c
					where r.ca_type = '%s'  and r.ca_id = c.id";
			$queryParams[] = $caType;
			
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		} else {
			$queryParams = array();
			$sql = "select r.id, r.ca_id, c.code, c.name, r.act_money, r.balance_money, r.rv_money
					from t_receivables r, t_supplier c
					where r.ca_type = '%s' and r.ca_id = c.id ";
			$queryParams[] = $caType;
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$sql .= " order by c.code
					limit %d , %d ";
			$queryParams[] = $start;
			$queryParams[] = $limit;
			$data = $db->query($sql, $queryParams);
			$result = array();
			
			foreach ( $data as $i => $v ) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
				$result[$i]["rvMoney"] = $v["rv_money"];
			}
			
			$queryParams = array();
			$sql = "select count(*) as cnt
					from t_receivables r, t_supplier c
					where r.ca_type = '%s'  and r.ca_id = c.id";
			$queryParams[] = $caType;
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::RECEIVING, "c", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			if ($categoryId) {
				$sql .= " and c.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		}
	}
}