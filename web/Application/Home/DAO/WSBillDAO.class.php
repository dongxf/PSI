<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 销售出库单 DAO
 *
 * @author 李静波
 */
class WSBillDAO extends PSIBaseExDAO {

	/**
	 * 获得销售出库单主表列表
	 */
	public function wsbillList($params) {
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
		$customerId = $params["customerId"];
		$sn = $params["sn"];
		$receivingType = $params["receivingType"];
		
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name,
					user.name as input_user_name, h.name as warehouse_name, w.sale_money,
					w.bill_status, w.date_created, w.receiving_type, w.memo
				from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				  and (w.input_user_id = user.id) and (w.warehouse_id = h.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::WAREHOUSING_SALE, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (w.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParams[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($sn) {
			$sql .= " and (w.id in
					(select d.wsbill_id from t_ws_bill_detail d
					 where d.sn_note like '%s'))";
			$queryParams[] = "%$sn%";
		}
		if ($receivingType != - 1) {
			$sql .= " and (w.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$sql .= " order by w.bizdt desc, w.ref desc
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待出库" : "已出库";
			$result[$i]["amount"] = $v["sale_money"];
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["receivingType"] = $v["receiving_type"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		$sql = "select count(*) as cnt
				from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h
				where (w.customer_id = c.id) and (w.biz_user_id = u.id)
				  and (w.input_user_id = user.id) and (w.warehouse_id = h.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::WAREHOUSING_SALE, "w", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (w.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (w.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($fromDT) {
			$sql .= " and (w.bizdt >= '%s') ";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (w.bizdt <= '%s') ";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (w.customer_id = '%s') ";
			$queryParams[] = $customerId;
		}
		if ($warehouseId) {
			$sql .= " and (w.warehouse_id = '%s') ";
			$queryParams[] = $warehouseId;
		}
		if ($sn) {
			$sql .= " and (w.id in
					(select d.wsbill_id from t_ws_bill_detail d
					 where d.sn_note like '%s'))";
			$queryParams[] = "%$sn%";
		}
		if ($receivingType != - 1) {
			$sql .= " and (w.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 获得某个销售出库单的明细记录列表
	 */
	public function wsBillDetailList($params) {
		$db = $this->db;
		
		$billId = $params["billId"];
		$sql = "select d.id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count,
				d.goods_price, d.goods_money, d.sn_note, d.memo
				from t_ws_bill_detail d, t_goods g, t_goods_unit u
				where d.wsbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id
				order by d.show_order";
		$data = $db->query($sql, $billId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsPrice"] = $v["goods_price"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["sn"] = $v["sn_note"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		return $result;
	}

	/**
	 * 新建销售出库单
	 * 
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function addWSBill(& $bill) {
		$db = $this->db;
		
		// 操作成功
		return null;
	}
}