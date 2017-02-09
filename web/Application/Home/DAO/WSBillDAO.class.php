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
	 * 生成新的销售出库单单号
	 *
	 * @param string $companyId        	
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getWSBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_ws_bill where ref like '%s' order by ref desc limit 1";
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
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$customerId = $bill["customerId"];
		$bizUserId = $bill["bizUserId"];
		$receivingType = $bill["receivingType"];
		$billMemo = $bill["billMemo"];
		$items = $bill["items"];
		
		$sobillRef = $bill["sobillRef"];
		
		// 检查客户
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("选择的客户不存在，无法保存数据");
		}
		
		// 检查仓库
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在，无法保存数据");
		}
		
		// 检查业务员
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务员不存在，无法保存数据");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$dataOrg = $bill["dataOrg"];
		$companyId = $bill["companyId"];
		$loginUserId = $bill["loginUserId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		// 主表
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		$sql = "insert into t_ws_bill(id, bill_status, bizdt, biz_user_id, customer_id,  date_created,
					input_user_id, ref, warehouse_id, receiving_type, data_org, company_id, memo)
				values ('%s', 0, '%s', '%s', '%s', now(), '%s', '%s', '%s', %d, '%s', '%s', '%s')";
		
		$rc = $db->execute($sql, $id, $bizDT, $bizUserId, $customerId, $loginUserId, $ref, 
				$warehouseId, $receivingType, $dataOrg, $companyId, $billMemo);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细表
		$sql = "insert into t_ws_bill_detail (id, date_created, goods_id,
				goods_count, goods_price, goods_money,
				show_order, wsbill_id, sn_note, data_org, memo, company_id)
				values ('%s', now(), '%s', %d, %f, %f, %d, '%s', '%s', '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if ($goodsId) {
				$goodsCount = intval($v["goodsCount"]);
				$goodsPrice = floatval($v["goodsPrice"]);
				$goodsMoney = floatval($v["goodsMoney"]);
				
				$sn = $v["sn"];
				$memo = $v["memo"];
				
				$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
						$goodsMoney, $i, $id, $sn, $dataOrg, $memo, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		}
		$sql = "select sum(goods_money) as sum_goods_money from t_ws_bill_detail where wsbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$sumGoodsMoney = $data[0]["sum_goods_money"];
		if (! $sumGoodsMoney) {
			$sumGoodsMoney = 0;
		}
		
		$sql = "update t_ws_bill set sale_money = %f where id = '%s' ";
		$rc = $db->execute($sql, $sumGoodsMoney, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		if ($sobillRef) {
			// 从销售订单生成销售出库单
			$sql = "select id, company_id from t_so_bill where ref = '%s' ";
			$data = $db->query($sql, $sobillRef);
			if (! $data) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			$sobillId = $data[0]["id"];
			$companyId = $data[0]["company_id"];
			
			$sql = "update t_ws_bill
					set company_id = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $companyId, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			$sql = "insert into t_so_ws(so_id, ws_id) values('%s', '%s')";
			$rc = $db->execute($sql, $sobillId, $id);
			if (! $rc) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	public function getWSBillById($id) {
		$db = $this->db;
		
		$sql = "select ref, bill_status, data_org, company_id from t_ws_bill where id = '%s' ";
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
	 * 编辑销售出库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function updateWSBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$customerId = $bill["customerId"];
		$bizUserId = $bill["bizUserId"];
		$receivingType = $bill["receivingType"];
		$billMemo = $bill["billMemo"];
		$items = $bill["items"];
		
		// 检查客户
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("选择的客户不存在，无法保存数据");
		}
		
		// 检查仓库
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("选择的仓库不存在，无法保存数据");
		}
		
		// 检查业务员
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("选择的业务员不存在，无法保存数据");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$oldBill = $this->getWSBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的销售出库单不存在");
		}
		$ref = $oldBill["ref"];
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("销售出库单[单号：{$ref}]已经提交出库了，不能再编辑");
		}
		$dataOrg = $oldBill["dataOrg"];
		$companyId = $oldBill["companyId"];
		
		$sql = "delete from t_ws_bill_detail where wsbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "insert into t_ws_bill_detail (id, date_created, goods_id,
				goods_count, goods_price, goods_money,
				show_order, wsbill_id, sn_note, data_org, memo, company_id)
				values ('%s', now(), '%s', %d, %f, %f, %d, '%s', '%s', '%s', '%s', '%s')";
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if ($goodsId) {
				$goodsCount = intval($v["goodsCount"]);
				$goodsPrice = floatval($v["goodsPrice"]);
				$goodsMoney = floatval($v["goodsMoney"]);
				
				$sn = $v["sn"];
				$memo = $v["memo"];
				
				$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
						$goodsMoney, $i, $id, $sn, $dataOrg, $memo, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		}
		$sql = "select sum(goods_money) as sum_goods_money from t_ws_bill_detail where wsbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$sumGoodsMoney = $data[0]["sum_goods_money"];
		if (! $sumGoodsMoney) {
			$sumGoodsMoney = 0;
		}
		
		$sql = "update t_ws_bill
				set sale_money = %f, customer_id = '%s', warehouse_id = '%s',
				biz_user_id = '%s', bizdt = '%s', receiving_type = %d,
				memo = '%s'
				where id = '%s' ";
		$rc = $db->execute($sql, $sumGoodsMoney, $customerId, $warehouseId, $bizUserId, $bizDT, 
				$receivingType, $billMemo, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["ref"] = $ref;
		// 操作成功
		return null;
	}
}