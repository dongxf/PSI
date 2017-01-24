<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 采购入库单 DAO
 *
 * @author 李静波
 */
class PWBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的采购入库单单号
	 */
	private function genNewBillRef($bill) {
		$db = $this->db;
		
		$companyId = $bill["companyId"];
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getPWBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_pw_bill where ref like '%s' order by ref desc limit 1";
		$data = $db->query($sql, $pre . $mid . "%");
		$suf = "001";
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, strlen($pre . $mid))) + 1;
			$suf = str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	/**
	 * 获得采购入库单主表列表
	 */
	public function pwbillList($params) {
		$db = $this->db;
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$warehouseId = $params["warehouseId"];
		$supplierId = $params["supplierId"];
		$paymentType = $params["paymentType"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$queryParams = array();
		$sql = "select p.id, p.bill_status, p.ref, p.biz_dt, u1.name as biz_user_name, u2.name as input_user_name,
					p.goods_money, w.name as warehouse_name, s.name as supplier_name,
					p.date_created, p.payment_type
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id)
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) ";
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_WAREHOUSE, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$sql .= " order by p.biz_dt desc, p.ref desc
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = $this->toYMD($v["biz_dt"]);
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待入库" : "已入库";
			$result[$i]["amount"] = $v["goods_money"];
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["paymentType"] = $v["payment_type"];
		}
		
		$sql = "select count(*) as cnt
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id)
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_WAREHOUSE, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		if ($billStatus != - 1) {
			$sql .= " and (p.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (p.biz_dt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.biz_dt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s') ";
			$queryParams[] = $supplierId;
		}
		if ($warehouseId) {
			$sql .= " and (p.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 获得采购入库单商品明细记录列表
	 */
	public function pwBillDetailList($params) {
		$pwbillId = $params["id"];
		
		$db = $this->db;
		
		$sql = "select p.id, g.code, g.name, g.spec, u.name as unit_name, p.goods_count, p.goods_price,
					p.goods_money, p.memo
				from t_pw_bill_detail p, t_goods g, t_goods_unit u
				where p.pwbill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order ";
		$data = $db->query($sql, $pwbillId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["goodsPrice"] = $v["goods_price"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		return $result;
	}
}