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
		if ($this->loginUserIdNotExists($loginUserId)) {
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

	/**
	 * 应收账款的明细记录
	 */
	public function rvDetailList($params) {
		$db = $this->db;
		
		$caType = $params["caType"];
		$caId = $params["caId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$sql = "select id, rv_money, act_money, balance_money, ref_type, ref_number, date_created, biz_date
				from t_receivables_detail
				where ca_type = '%s' and ca_id = '%s'
				order by biz_date desc, date_created desc
				limit %d , %d ";
		$data = $db->query($sql, $caType, $caId, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["refType"] = $v["ref_type"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["bizDT"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["rvMoney"] = $v["rv_money"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}
		
		$sql = "select count(*) as cnt
				from t_receivables_detail
				where ca_type = '%s' and ca_id = '%s' ";
		$data = $db->query($sql, $caType, $caId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 应收账款的收款记录
	 */
	public function rvRecordList($params) {
		$db = $this->db;
		
		$refType = $params["refType"];
		$refNumber = $params["refNumber"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$sql = "select r.id, r.act_money, r.biz_date, r.date_created, r.remark, u.name as rv_user_name,
				user.name as input_user_name
				from t_receiving r, t_user u, t_user user
				where r.rv_user_id = u.id and r.input_user_id = user.id
				  and r.ref_type = '%s' and r.ref_number = '%s'
				order by r.date_created desc
				limit %d , %d ";
		$data = $db->query($sql, $refType, $refNumber, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["actMoney"] = $v["act_money"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["biz_date"]));
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["bizUserName"] = $v["rv_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["remark"] = $v["remark"];
		}
		
		$sql = "select count(*) as cnt
				from t_receiving
				where ref_type = '%s' and ref_number = '%s' ";
		$data = $db->query($sql, $refType, $refNumber);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}