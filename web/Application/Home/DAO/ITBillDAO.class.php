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
	 * 生成新的调拨单单号
	 *
	 * @param string $companyId        	
	 *
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getITBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_it_bill where ref like '%s' order by ref desc limit 1";
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
	 * 调拨单主表列表信息
	 *
	 * @param array $params        	
	 * @return array
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

	/**
	 * 调拨单的明细记录
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function itBillDetailList($params) {
		$db = $this->db;
		
		// id: 调拨单id
		$id = $params["id"];
		
		$result = array();
		
		$sql = "select t.id, g.code, g.name, g.spec, u.name as unit_name, t.goods_count
				from t_it_bill_detail t, t_goods g, t_goods_unit u
				where t.itbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
		
		$data = $db->query($sql, $id);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["goodsCount"] = $v["goods_count"];
		}
		
		return $result;
	}

	/**
	 * 新建调拨单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function addITBill(& $bill) {
		$db = $this->db;
		
		$bizDT = $bill["bizDT"];
		$fromWarehouseId = $bill["fromWarehouseId"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$fromWarehouse = $warehouseDAO->getWarehouseById($fromWarehouseId);
		if (! $fromWarehouse) {
			return $this->bad("调出仓库不存在，无法保存");
		}
		
		$toWarehouseId = $bill["toWarehouseId"];
		$toWarehouse = $warehouseDAO->getWarehouseById($toWarehouseId);
		if (! $toWarehouse) {
			return $this->bad("调入仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在，无法保存");
		}
		
		if ($fromWarehouseId == $toWarehouseId) {
			return $this->bad("调出仓库和调入仓库不能是同一个仓库");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$items = $bill["items"];
		
		$dataOrg = $bill["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		// 新增
		$sql = "insert into t_it_bill(id, bill_status, bizdt, biz_user_id,
					date_created, input_user_id, ref, from_warehouse_id,
					to_warehouse_id, data_org, company_id)
				values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s', '%s', '%s', '%s')";
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		
		$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $loginUserId, $ref, $fromWarehouseId, 
				$toWarehouseId, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count,
					show_order, itbill_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %d, '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			
			$goodsCount = $v["goodsCount"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $i, $id, $dataOrg, 
					$companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	public function getITBillById($id) {
		$db = $this->db;
		
		$sql = "select ref, bill_status, data_org, company_id from t_it_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		} else {
			return array(
					"ref" => $data[0]["ref"],
					"billStatus" => $data[0]["bill_status"],
					"dataOrg" => $data[0]["data_org"],
					"companyId" => $data[0]["company_id"]
			);
		}
	}

	/**
	 * 编辑调拨单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function updateITBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$oldBill = $this->getITBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的调拨单不存在");
		}
		$ref = $oldBill["ref"];
		$dataOrg = $oldBill["dataOrg"];
		$companyId = $oldBill["companyId"];
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("调拨单(单号：$ref)已经提交，不能被编辑");
		}
		
		$bizDT = $bill["bizDT"];
		$fromWarehouseId = $bill["fromWarehouseId"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$fromWarehouse = $warehouseDAO->getWarehouseById($fromWarehouseId);
		if (! $fromWarehouse) {
			return $this->bad("调出仓库不存在，无法保存");
		}
		
		$toWarehouseId = $bill["toWarehouseId"];
		$toWarehouse = $warehouseDAO->getWarehouseById($toWarehouseId);
		if (! $toWarehouse) {
			return $this->bad("调入仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在，无法保存");
		}
		
		if ($fromWarehouseId == $toWarehouseId) {
			return $this->bad("调出仓库和调入仓库不能是同一个仓库");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$items = $bill["items"];
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$sql = "update t_it_bill
				set bizdt = '%s', biz_user_id = '%s', date_created = now(),
				    input_user_id = '%s', from_warehouse_id = '%s', to_warehouse_id = '%s'
				where id = '%s' ";
		
		$rc = $db->execute($sql, $bizDT, $bizUserId, $loginUserId, $fromWarehouseId, $toWarehouseId, 
				$id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细记录
		$sql = "delete from t_it_bill_detail where itbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "insert into t_it_bill_detail(id, date_created, goods_id, goods_count,
					show_order, itbill_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %d, '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			
			$goodsCount = $v["goodsCount"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $i, $id, $dataOrg, 
					$companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 删除调拨单
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deleteITBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getITBillById($id);
		if (! $bill) {
			return $this->bad("要删除的调拨单不存在");
		}
		
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		
		if ($billStatus != 0) {
			return $this->bad("调拨单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_it_bill_detail where itbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_it_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}
}