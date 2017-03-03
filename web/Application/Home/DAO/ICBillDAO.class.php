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

	/**
	 * 新建盘点单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function addICBill(& $bill) {
		$db = $this->db;
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("盘点仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在，无法保存");
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
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		
		// 主表
		$sql = "insert into t_ic_bill(id, bill_status, bizdt, biz_user_id, date_created,
					input_user_id, ref, warehouse_id, data_org, company_id)
				values ('%s', 0, '%s', '%s', now(), '%s', '%s', '%s', '%s', '%s')";
		$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $loginUserId, $ref, $warehouseId, 
				$dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细表
		$sql = "insert into t_ic_bill_detail(id, date_created, goods_id, goods_count, goods_money,
					show_order, icbill_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %f, %d, '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			$goodsCount = $v["goodsCount"];
			$goodsMoney = $v["goodsMoney"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, $i, $id, 
					$dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	public function getICBillById($id) {
		$db = $this->db;
		$sql = "select ref, bill_status, data_org, company_id from t_ic_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return array(
					"ref" => $data[0]["ref"],
					"billStatus" => $data[0]["bill_status"],
					"dataOrg" => $data[0]["data_org"],
					"companyId" => $data[0]["company_id"]
			);
		} else {
			return null;
		}
	}

	/**
	 * 编辑盘点单
	 */
	public function updateICBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("盘点仓库不存在，无法保存");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在，无法保存");
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
		
		$oldBill = $this->getICBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的盘点单不存在，无法保存");
		}
		
		$ref = $oldBill["ref"];
		$dataOrg = $oldBill["dataOrg"];
		$companyId = $oldBill["companyId"];
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("盘点单(单号：$ref)已经提交，不能再编辑");
		}
		
		// 主表
		$sql = "update t_ic_bill
				set bizdt = '%s', biz_user_id = '%s', date_created = now(),
					input_user_id = '%s', warehouse_id = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $bizDT, $bizUserId, $loginUserId, $warehouseId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细表
		$sql = "delete from t_ic_bill_detail where icbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "insert into t_ic_bill_detail(id, date_created, goods_id, goods_count, goods_money,
					show_order, icbill_id, data_org, company_id)
				values ('%s', now(), '%s', %d, %f, %d, '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			$goodsCount = $v["goodsCount"];
			$goodsMoney = $v["goodsMoney"];
			
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, $i, $id, 
					$dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 获得某个盘点单的详情
	 */
	public function icBillInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$result = array();
		
		if ($id) {
			// 编辑
			$sql = "select t.ref, t.bill_status, t.bizdt, t.biz_user_id, u.name as biz_user_name,
						w.id as warehouse_id, w.name as warehouse_name
					from t_ic_bill t, t_user u, t_warehouse w
					where t.id = '%s' and t.biz_user_id = u.id
					      and t.warehouse_id = w.id";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $result;
			}
			
			$result["bizUserId"] = $data[0]["biz_user_id"];
			$result["bizUserName"] = $data[0]["biz_user_name"];
			$result["ref"] = $data[0]["ref"];
			$result["billStatus"] = $data[0]["bill_status"];
			$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
			$result["warehouseId"] = $data[0]["warehouse_id"];
			$result["warehouseName"] = $data[0]["warehouse_name"];
			
			$items = array();
			$sql = "select t.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name,
						t.goods_count, t.goods_money
				from t_ic_bill_detail t, t_goods g, t_goods_unit u
				where t.icbill_id = '%s' and t.goods_id = g.id and g.unit_id = u.id
				order by t.show_order ";
			
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
			}
			
			$result["items"] = $items;
		} else {
			// 新建
			$result["bizUserId"] = $params["loginUserId"];
			$result["bizUserName"] = $params["loginUserName"];
		}
		
		return $result;
	}

	/**
	 * 删除盘点单
	 */
	public function deleteICBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getICBillById($id);
		
		if (! $bill) {
			return $this->bad("要删除的盘点单不存在");
		}
		
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		
		if ($billStatus != 0) {
			return $this->bad("盘点单(单号：$ref)已经提交，不能被删除");
		}
		
		$sql = "delete from t_ic_bill_detail where icbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_ic_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}
}