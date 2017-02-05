<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 采购退货出库单 DAO
 *
 * @author 李静波
 */
class PRBillDAO extends PSIBaseExDAO {

	public function selectPWBillList($params) {
		$db = $this->db;
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$ref = $params["ref"];
		$supplierId = $params["supplierId"];
		$warehouseId = $params["warehouseId"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$result = array();
		
		$sql = "select p.id, p.ref, p.biz_dt, s.name as supplier_name, p.goods_money,
					w.name as warehouse_name, u1.name as biz_user_name, u2.name as input_user_name
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id)
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$sql .= " order by p.ref desc limit %d, %d";
		$queryParamas[] = $start;
		$queryParamas[] = $limit;
		
		$data = $db->query($sql, $queryParamas);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = $this->toYMD($v["biz_dt"]);
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["amount"] = $v["goods_money"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u1, t_user u2
				where (p.supplier_id = s.id)
					and (p.warehouse_id = w.id)
					and (p.biz_user_id = u1.id)
					and (p.input_user_id = u2.id)
					and (p.bill_status = 1000)";
		$queryParamas = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_REJECTION, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParamas = $rs[1];
		}
		
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParamas[] = "%$ref%";
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParamas[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParamas[] = $warehouseId;
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParamas[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParamas[] = $toDT;
		}
		
		$data = $db->query($sql, $queryParamas);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}