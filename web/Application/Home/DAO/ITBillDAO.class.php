<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 库间调拨 DAO
 *
 * @author 李静波
 */
class ITBillDAO extends PSIBaseExDAO {

	/**
	 * 调拨单主表列表信息
	 */
	public function itbillList($params) {
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
		$fromWarehouseId = $params["fromWarehouseId"];
		$toWarehouseId = $params["toWarehouseId"];
		
		$sql = "select t.id, t.ref, t.bizdt, t.bill_status,
					fw.name as from_warehouse_name,
					tw.name as to_warehouse_name,
					u.name as biz_user_name,
					u1.name as input_user_name,
					t.date_created
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where (t.from_warehouse_id = fw.id)
				  and (t.to_warehouse_id = tw.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id) ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::INVENTORY_TRANSFER, "t", $loginUserId);
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
		if ($fromWarehouseId) {
			$sql .= " and (t.from_warehouse_id = '%s') ";
			$queryParams[] = $fromWarehouseId;
		}
		if ($toWarehouseId) {
			$sql .= " and (t.to_warehouse_id = '%s') ";
			$queryParams[] = $toWarehouseId;
		}
		
		$sql .= " order by t.bizdt desc, t.ref desc
				limit %d , %d
				";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["bizdt"]));
			$result[$i]["billStatus"] = $v["bill_status"] == 0 ? "待调拨" : "已调拨";
			$result[$i]["fromWarehouseName"] = $v["from_warehouse_name"];
			$result[$i]["toWarehouseName"] = $v["to_warehouse_name"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
		}
		
		$sql = "select count(*) as cnt
				from t_it_bill t, t_warehouse fw, t_warehouse tw,
				   t_user u, t_user u1
				where (t.from_warehouse_id = fw.id)
				  and (t.to_warehouse_id = tw.id)
				  and (t.biz_user_id = u.id)
				  and (t.input_user_id = u1.id)
				";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::INVENTORY_TRANSFER, "t", $loginUserId);
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
		if ($fromWarehouseId) {
			$sql .= " and (t.from_warehouse_id = '%s') ";
			$queryParams[] = $fromWarehouseId;
		}
		if ($toWarehouseId) {
			$sql .= " and (t.to_warehouse_id = '%s') ";
			$queryParams[] = $toWarehouseId;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}