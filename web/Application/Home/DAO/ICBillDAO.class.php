<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 盘点单 DAO
 *
 * @author 李静波
 */
class ICBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的盘点单单号
	 *
	 * @param string $companyId        	
	 *
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getICBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_ic_bill where ref like '%s' order by ref desc limit 1";
		$data = $db->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, strlen($pre . $mid))) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	/**
	 * 盘点单列表
	 */
	public function icbillList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		
		$sql = "select t.id, t.ref, t.bizdt, t.bill_status,
				w.name as warehouse_name,
				u.name as biz_user_name,
				u1.name as input_user_name,
				t.date_created
				from t_ic_bill t, t_warehouse w, t_user u, t_user u1
				where (t.warehouse_id = w.id)
				and (t.biz_user_id = u.id)
				and (t.input_user_id = u1.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::INVENTORY_CHECK, "t", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (t.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (t.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (t.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (t.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($warehouseId) {
			$sql .= " and (t.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		
		$sql .= " order by t.bizdt desc, t.ref desc
			limit %d , %d ";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待盘点" : "已盘点";
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
		}
		
		$sql = "select count(*) as cnt
				from t_ic_bill t, t_warehouse w, t_user u, t_user u1
				where (t.warehouse_id = w.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id)
				";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::INVENTORY_CHECK, "t", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (t.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (t.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (t.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (t.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($warehouseId) {
			$sql .= " and (t.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 盘点单明细记录
	 */
	public function icBillDetailList($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = array();
		
		$sql = "select t.id, g.code, g.name, g.spec, u.name as unit_name, t.goods_count, t.goods_money
				from t_ic_bill_detail t, t_goods g, t_goods_unit u
				where t.icbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
		
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
		}
		
		return $result;
	}
}