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

	/**
	 * 应付账款列表
	 */
	public function payList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$caType = $params["caType"];
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		if ($caType == "supplier") {
			$queryParams = array();
			$sql = "select p.id, p.pay_money, p.act_money, p.balance_money, s.id as ca_id, s.code, s.name
					from t_payables p, t_supplier s
					where p.ca_id = s.id and p.ca_type = 'supplier' ";
			if ($categoryId) {
				$sql .= " and s.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "s", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			$sql .= " order by s.code
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
				$result[$i]["payMoney"] = $v["pay_money"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
			}
			
			$queryParams[] = array();
			$sql = "select count(*) as cnt from t_payables p, t_supplier s
					where p.ca_id = s.id and p.ca_type = 'supplier' ";
			if ($categoryId) {
				$sql .= " and s.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "s", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		} else {
			$queryParams = array();
			$sql = "select p.id, p.pay_money, p.act_money, p.balance_money, s.id as ca_id, s.code, s.name
					from t_payables p, t_customer s
					where p.ca_id = s.id and p.ca_type = 'customer' ";
			if ($categoryId) {
				$sql .= " and s.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "s", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			$sql .= " order by s.code
					limit %d , %d";
			$queryParams[] = $start;
			$queryParams[] = $limit;
			$data = $db->query($sql, $queryParams);
			$result = array();
			foreach ( $data as $i => $v ) {
				$result[$i]["id"] = $v["id"];
				$result[$i]["caId"] = $v["ca_id"];
				$result[$i]["code"] = $v["code"];
				$result[$i]["name"] = $v["name"];
				$result[$i]["payMoney"] = $v["pay_money"];
				$result[$i]["actMoney"] = $v["act_money"];
				$result[$i]["balanceMoney"] = $v["balance_money"];
			}
			
			$queryParams = array();
			$sql = "select count(*) as cnt from t_payables p, t_customer s
					where p.ca_id = s.id and p.ca_type = 'customer' ";
			if ($categoryId) {
				$sql .= " and s.category_id = '%s' ";
				$queryParams[] = $categoryId;
			}
			$ds = new DataOrgDAO($db);
			$rs = $ds->buildSQL(FIdConst::PAYABLES, "s", $loginUserId);
			if ($rs) {
				$sql .= " and " . $rs[0];
				$queryParams = array_merge($queryParams, $rs[1]);
			}
			$data = $db->query($sql, $queryParams);
			$cnt = $data[0]["cnt"];
			
			return array(
					"dataList" => $result,
					"totalCount" => $cnt
			);
		}
	}

	/**
	 * 每笔应付账款的明细记录
	 */
	public function payDetailList($params) {
		$db = $this->db;
		
		$caType = $params["caType"];
		$caId = $params["caId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$sql = "select id, ref_type, ref_number, pay_money, act_money, balance_money, date_created, biz_date
				from t_payables_detail
				where ca_type = '%s' and ca_id = '%s'
				order by biz_date desc, date_created desc
				limit %d , %d ";
		$data = $db->query($sql, $caType, $caId, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["refType"] = $v["ref_type"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["bizDT"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["payMoney"] = $v["pay_money"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}
		
		$sql = "select count(*) as cnt from t_payables_detail
				where ca_type = '%s' and ca_id = '%s' ";
		$data = $db->query($sql, $caType, $caId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}