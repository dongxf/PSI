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
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
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

	/**
	 * 新建采购入库单
	 */
	public function addPWBill(& $bill) {
		$db = $this->db;
		
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$supplierId = $bill["supplierId"];
		$bizUserId = $bill["bizUserId"];
		$paymentType = $bill["paymentType"];
		
		$pobillRef = $bill["pobillRef"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("入库仓库不存在");
		}
		
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$dataOrg = $bill["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$ref = $this->genNewBillRef($companyId);
		
		$id = $this->newId();
		
		$sql = "insert into t_pw_bill (id, ref, supplier_id, warehouse_id, biz_dt,
				biz_user_id, bill_status, date_created, goods_money, input_user_id, payment_type,
				data_org, company_id)
				values ('%s', '%s', '%s', '%s', '%s', '%s', 0, now(), 0, '%s', %d, '%s', '%s')";
		
		$rc = $db->execute($sql, $id, $ref, $supplierId, $warehouseId, $bizDT, $bizUserId, 
				$loginUserId, $paymentType, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		$goodsDAO = new GoodsDAO($db);
		
		// 明细记录
		$items = $bill["items"];
		foreach ( $items as $i => $item ) {
			$goodsId = $item["goodsId"];
			if ($goodsId == null) {
				continue;
			}
			
			// 检查商品是否存在
			$goods = $goodsDAO->getGoodsById($goodsId);
			if (! $goods) {
				return $this->bad("选择的商品不存在");
			}
			
			$goodsCount = intval($item["goodsCount"]);
			if ($goodsCount == 0) {
				return $this->bad("入库数量不能为0");
			}
			
			$memo = $item["memo"];
			$goodsPrice = $item["goodsPrice"];
			$goodsMoney = $item["goodsMoney"];
			
			$sql = "insert into t_pw_bill_detail
						(id, date_created, goods_id, goods_count, goods_price,
						goods_money,  pwbill_id, show_order, data_org, memo, company_id)
					values ('%s', now(), '%s', %d, %f, %f, '%s', %d, '%s', '%s', '%s')";
			$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, 
					$goodsMoney, $id, $i, $dataOrg, $memo, $companyId);
			if ($rc === false) {
				return $this->sqlError(__LINE__);
			}
		}
		
		$sql = "select sum(goods_money) as goods_money from t_pw_bill_detail
				where pwbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$totalMoney = $data[0]["goods_money"];
		if (! $totalMoney) {
			$totalMoney = 0;
		}
		$sql = "update t_pw_bill
				set goods_money = %f
				where id = '%s' ";
		$rc = $db->execute($sql, $totalMoney, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		if ($pobillRef) {
			// 从采购订单生成采购入库单
			$sql = "select id, company_id from t_po_bill where ref = '%s' ";
			$data = $db->query($sql, $pobillRef);
			if (! $data) {
				return $this->sqlError(__LINE__);
			}
			$pobillId = $data[0]["id"];
			$companyId = $data[0]["company_id"];
			
			$sql = "update t_pw_bill
					set company_id = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $companyId, $id);
			if ($rc === false) {
				return $this->sqlError(__LINE__);
			}
			
			$sql = "insert into t_po_pw(po_id, pw_id) values('%s', '%s')";
			$rc = $db->execute($sql, $pobillId, $id);
			if (! $rc) {
				return $this->sqlError(__LINE__);
			}
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑采购入库单
	 */
	public function updatePWBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$supplierId = $bill["supplierId"];
		$bizUserId = $bill["bizUserId"];
		$paymentType = $bill["paymentType"];
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("入库仓库不存在");
		}
		
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务人员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$oldBill = $this->getPWBillById($id);
		if (! $oldBill) {
			return $this->bad("要编辑的采购入库单不存在");
		}
		$dataOrg = $oldBill["dataOrg"];
		$billStatus = $oldBill["billStatus"];
		$companyId = $oldBill["companyId"];
		$ref = $oldBill["ref"];
		if ($billStatus != 0) {
			return $this->bad("当前采购入库单已经提交入库，不能再编辑");
		}
		$bill["ref"] = $ref;
		
		$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		$goodsDAO = new GoodsDAO($db);
		
		// 明细记录
		$items = $bill["items"];
		foreach ( $items as $i => $item ) {
			$goodsId = $item["goodsId"];
			
			if ($goodsId == null) {
				continue;
			}
			
			$goods = $goodsDAO->getGoodsById($goodsId);
			if (! $goods) {
				return $this->bad("选择的商品不存在");
			}
			
			$goodsCount = intval($item["goodsCount"]);
			if ($goodsCount == 0) {
				return $this->bad("入库数量不能为0");
			}
			
			$memo = $item["memo"];
			$goodsPrice = $item["goodsPrice"];
			$goodsMoney = $item["goodsMoney"];
			
			$sql = "insert into t_pw_bill_detail (id, date_created, goods_id, goods_count, goods_price,
									goods_money,  pwbill_id, show_order, data_org, memo, company_id)
									values ('%s', now(), '%s', %d, %f, %f, '%s', %d, '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
					$goodsMoney, $id, $i, $dataOrg, $memo, $companyId);
			if ($rc === false) {
				return $this->sqlError(__LINE__);
			}
		}
		
		$sql = "select sum(goods_money) as goods_money from t_pw_bill_detail
				where pwbill_id = '%s' ";
		$data = $db->query($sql, $id);
		$totalMoney = $data[0]["goods_money"];
		if (! $totalMoney) {
			$totalMoney = 0;
		}
		$sql = "update t_pw_bill
				set goods_money = %f, warehouse_id = '%s',
					supplier_id = '%s', biz_dt = '%s',
					biz_user_id = '%s', payment_type = %d
				where id = '%s' ";
		$rc = $db->execute($sql, $totalMoney, $warehouseId, $supplierId, $bizDT, $bizUserId, 
				$paymentType, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		return null;
	}

	public function getPWBillById($id) {
		$sql = "select ref, bill_status, data_org, company_id, warehouse_id 
				from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		} else {
			return array(
					"ref" => $data[0]["ref"],
					"billStatus" => $data[0]["bill_status"],
					"dataOrg" => $data[0]["data_org"],
					"companyId" => $data[0]["company_id"],
					"warehouseId" => $data[0]["warehouse_id"]
			);
		}
	}

	/**
	 * 同步在途库存
	 */
	public function updateAfloatInventoryByPWBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		$warehouseId = $bill["warehouseId"];
		
		$sql = "select goods_id
				from t_pw_bill_detail
				where pwbill_id = '%s'
				order by show_order";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$goodsId = $v["goods_id"];
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		return null;
	}

	private function updateAfloatInventory($db, $warehouseId, $goodsId) {
		$sql = "select sum(pd.goods_count) as goods_count, sum(pd.goods_money) as goods_money
				from t_pw_bill p, t_pw_bill_detail pd
				where p.id = pd.pwbill_id
					and p.warehouse_id = '%s'
					and pd.goods_id = '%s'
					and p.bill_status = 0 ";
		
		$data = $db->query($sql, $warehouseId, $goodsId);
		$count = 0;
		$price = 0;
		$money = 0;
		if ($data) {
			$count = $data[0]["goods_count"];
			if (! $count) {
				$count = 0;
			}
			$money = $data[0]["goods_money"];
			if (! $money) {
				$money = 0;
			}
			
			if ($count !== 0) {
				$price = $money / $count;
			}
		}
		
		$sql = "select id from t_inventory where warehouse_id = '%s' and goods_id = '%s' ";
		$data = $db->query($sql, $warehouseId, $goodsId);
		if (! $data) {
			// 首次有库存记录
			$sql = "insert into t_inventory (warehouse_id, goods_id, afloat_count, afloat_price,
						afloat_money, balance_count, balance_price, balance_money)
					values ('%s', '%s', %d, %f, %f, 0, 0, 0)";
			return $db->execute($sql, $warehouseId, $goodsId, $count, $price, $money);
		} else {
			$sql = "update t_inventory
					set afloat_count = %d, afloat_price = %f, afloat_money = %f
					where warehouse_id = '%s' and goods_id = '%s' ";
			return $db->execute($sql, $count, $price, $money, $warehouseId, $goodsId);
		}
		
		return true;
	}

	/**
	 * 获得某个采购入库单的信息
	 */
	public function pwBillInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$pobillRef = $params["pobillRef"];
		
		$result = array(
				"id" => $id
		);
		
		$sql = "select p.ref, p.bill_status, p.supplier_id, s.name as supplier_name,
				p.warehouse_id, w.name as  warehouse_name,
				p.biz_user_id, u.name as biz_user_name, p.biz_dt, p.payment_type
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u
				where p.id = '%s' and p.supplier_id = s.id and p.warehouse_id = w.id
				  and p.biz_user_id = u.id";
		$data = $db->query($sql, $id);
		if ($data) {
			$v = $data[0];
			$result["ref"] = $v["ref"];
			$result["billStatus"] = $v["bill_status"];
			$result["supplierId"] = $v["supplier_id"];
			$result["supplierName"] = $v["supplier_name"];
			$result["warehouseId"] = $v["warehouse_id"];
			$result["warehouseName"] = $v["warehouse_name"];
			$result["bizUserId"] = $v["biz_user_id"];
			$result["bizUserName"] = $v["biz_user_name"];
			$result["bizDT"] = date("Y-m-d", strtotime($v["biz_dt"]));
			$result["paymentType"] = $v["payment_type"];
			
			// 采购的商品明细
			$items = array();
			$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name,
					p.goods_count, p.goods_price, p.goods_money, p.memo
					from t_pw_bill_detail p, t_goods g, t_goods_unit u
					where p.goods_Id = g.id and g.unit_id = u.id and p.pwbill_id = '%s'
					order by p.show_order";
			$data = $db->query($sql, $id);
			foreach ( $data as $i => $v ) {
				$items[$i]["id"] = $v["id"];
				$items[$i]["goodsId"] = $v["goods_id"];
				$items[$i]["goodsCode"] = $v["code"];
				$items[$i]["goodsName"] = $v["name"];
				$items[$i]["goodsSpec"] = $v["spec"];
				$items[$i]["unitName"] = $v["unit_name"];
				$items[$i]["goodsCount"] = $v["goods_count"];
				$items[$i]["goodsPrice"] = $v["goods_price"];
				$items[$i]["goodsMoney"] = $v["goods_money"];
				$items[$i]["memo"] = $v["memo"];
			}
			
			$result["items"] = $items;
			
			// 查询该单据是否是由采购订单生成的
			$sql = "select po_id from t_po_pw where pw_id = '%s' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$result["genBill"] = true;
			} else {
				$result["genBill"] = false;
			}
		} else {
			// 新建采购入库单
			$result["bizUserId"] = $params["loginUserId"];
			$result["bizUserName"] = $params["loginUserName"];
			
			$tc = new BizConfigDAO($db);
			$companyId = $params["companyId"];
			
			$warehouse = $tc->getPWBillDefaultWarehouse($companyId);
			if ($warehouse) {
				$result["warehouseId"] = $warehouse["id"];
				$result["warehouseName"] = $warehouse["name"];
			}
			
			if ($pobillRef) {
				// 由采购订单生成采购入库单
				$sql = "select p.id, p.supplier_id, s.name as supplier_name, p.deal_date,
							p.payment_type
						from t_po_bill p, t_supplier s
						where p.ref = '%s' and p.supplier_id = s.id ";
				$data = $db->query($sql, $pobillRef);
				if ($data) {
					$v = $data[0];
					$result["supplierId"] = $v["supplier_id"];
					$result["supplierName"] = $v["supplier_name"];
					$result["dealDate"] = $this->toYMD($v["deal_date"]);
					$result["paymentType"] = $v["payment_type"];
					
					$pobillId = $v["id"];
					// 采购的明细
					$items = array();
					$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name,
								p.goods_count, p.goods_price, p.goods_money
							from t_po_bill_detail p, t_goods g, t_goods_unit u
							where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
							order by p.show_order ";
					$data = $db->query($sql, $pobillId);
					foreach ( $data as $i => $v ) {
						$items[$i]["id"] = $v["id"];
						$items[$i]["goodsId"] = $v["goods_id"];
						$items[$i]["goodsCode"] = $v["code"];
						$items[$i]["goodsName"] = $v["name"];
						$items[$i]["goodsSpec"] = $v["spec"];
						$items[$i]["unitName"] = $v["unit_name"];
						$items[$i]["goodsCount"] = $v["goods_count"];
						$items[$i]["goodsPrice"] = $v["goods_price"];
						$items[$i]["goodsMoney"] = $v["goods_money"];
					}
					
					$result["items"] = $items;
				}
			} else {
				// 采购入库单默认付款方式
				$result["paymentType"] = $tc->getPWBillDefaultPayment($companyId);
			}
		}
		
		return $result;
	}

	/**
	 * 删除采购入库单
	 */
	public function deletePWBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getPWBillById($id);
		if (! $bill) {
			return $this->bad("要删除的采购入库单不存在");
		}
		
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("当前采购入库单已经提交入库，不能删除");
		}
		$warehouseId = $bill["warehouseId"];
		
		$sql = "select goods_id
				from t_pw_bill_detail
				where pwbill_id = '%s'
				order by show_order";
		$data = $db->query($sql, $id);
		$goodsIdList = array();
		foreach ( $data as $v ) {
			$goodsIdList[] = $v["goods_id"];
		}
		
		$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_pw_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		// 删除从采购订单生成的记录
		$sql = "delete from t_po_pw where pw_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__LINE__);
		}
		
		// 同步库存账中的在途库存
		foreach ( $goodsIdList as $v ) {
			$goodsId = $v;
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				return $this->sqlError(__LINE__);
			}
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}
}