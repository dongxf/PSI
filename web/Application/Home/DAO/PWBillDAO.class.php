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
	 *
	 * @param string $companyId        	
	 * @return string
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
	 *
	 * @param array $params        	
	 * @return array
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
	 *
	 * @param array $params        	
	 * @return array
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
		
		foreach ( $data as $v ) {
			$item = array(
					"id" => $v["id"],
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"unitName" => $v["unit_name"],
					"goodsCount" => $v["goods_count"],
					"goodsMoney" => $v["goods_money"],
					"goodsPrice" => $v["goods_price"],
					"memo" => $v["memo"]
			);
			
			$result[] = $item;
		}
		
		return $result;
	}

	/**
	 * 新建采购入库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
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
			return $this->sqlError(__METHOD__, __LINE__);
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
			
			$poBillDetailId = $item["poBillDetailId"];
			
			$sql = "insert into t_pw_bill_detail
					(id, date_created, goods_id, goods_count, goods_price,
					goods_money,  pwbill_id, show_order, data_org, memo, company_id,
					pobilldetail_id)
					values ('%s', now(), '%s', %d, %f, %f, '%s', %d, '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsPrice, 
					$goodsMoney, $id, $i, $dataOrg, $memo, $companyId, $poBillDetailId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		if ($pobillRef) {
			// 从采购订单生成采购入库单
			$sql = "select id, company_id from t_po_bill where ref = '%s' ";
			$data = $db->query($sql, $pobillRef);
			if (! $data) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			$pobillId = $data[0]["id"];
			$companyId = $data[0]["company_id"];
			
			$sql = "update t_pw_bill
					set company_id = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $companyId, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			$sql = "insert into t_po_pw(po_id, pw_id) values('%s', '%s')";
			$rc = $db->execute($sql, $pobillId, $id);
			if (! $rc) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑采购入库单
	 *
	 * @param array $bill        	
	 * @return NULL|array
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
			return $this->sqlError(__METHOD__, __LINE__);
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
				return $this->sqlError(__METHOD__, __LINE__);
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		return null;
	}

	/**
	 * 通过id查询采购入库单
	 *
	 * @param string $id
	 *        	采购入库单id
	 * @return NULL|array
	 */
	public function getPWBillById($id) {
		$db = $this->db;
		
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
	 *
	 * @param array $bill        	
	 * @return NULL|array
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
	 *
	 * @param array $params        	
	 *
	 * @return array
	 */
	public function pwBillInfo($params) {
		$db = $this->db;
		
		// id: 采购入库单id
		$id = $params["id"];
		// pobillRef: 采购订单单号，可以为空，为空表示直接录入采购入库单；不为空表示是从采购订单生成入库单
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
								p.goods_count, p.goods_price, p.goods_money, p.left_count
							from t_po_bill_detail p, t_goods g, t_goods_unit u
							where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
							order by p.show_order ";
					$data = $db->query($sql, $pobillId);
					foreach ( $data as $i => $v ) {
						$items[$i]["id"] = $v["id"];
						$items[$i]["poBillDetailId"] = $v["id"];
						$items[$i]["goodsId"] = $v["goods_id"];
						$items[$i]["goodsCode"] = $v["code"];
						$items[$i]["goodsName"] = $v["name"];
						$items[$i]["goodsSpec"] = $v["spec"];
						$items[$i]["unitName"] = $v["unit_name"];
						$items[$i]["goodsCount"] = $v["left_count"];
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
	 *
	 * @param array $params        	
	 * @return NULL|array
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_pw_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 删除从采购订单生成的记录
		$sql = "delete from t_po_pw where pw_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 同步库存账中的在途库存
		foreach ( $goodsIdList as $v ) {
			$goodsId = $v;
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 提交采购入库单
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function commitPWBill(& $params) {
		$db = $this->db;
		
		// id: 采购入库单id
		$id = $params["id"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$sql = "select ref, warehouse_id, bill_status, biz_dt, biz_user_id,  goods_money, supplier_id,
					payment_type, company_id
				from t_pw_bill
				where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return $this->bad("要提交的采购入库单不存在");
		}
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			return $this->bad("采购入库单已经提交入库，不能再次提交");
		}
		
		$ref = $data[0]["ref"];
		$bizDT = $data[0]["biz_dt"];
		$bizUserId = $data[0]["biz_user_id"];
		$billPayables = floatval($data[0]["goods_money"]);
		$supplierId = $data[0]["supplier_id"];
		$warehouseId = $data[0]["warehouse_id"];
		$paymentType = $data[0]["payment_type"];
		$companyId = $data[0]["company_id"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$bc = new BizConfigDAO($db);
		// true: 先进先出法
		$fifo = $bc->getInventoryMethod($companyId) == 1;
		
		$warehouseDAO = new WarehouseDAO($db);
		$warehouse = $warehouseDAO->getWarehouseById($warehouseId);
		if (! $warehouse) {
			return $this->bad("要入库的仓库不存在");
		}
		$inited = $warehouse["inited"];
		if ($inited == 0) {
			return $this->bad("仓库 [{$warehouse['name']}] 还没有完成建账，不能做采购入库的操作");
		}
		
		// 检查供应商是否存在
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在");
		}
		
		// 检查业务员是否存在
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$sql = "select goods_id, goods_count, goods_price, goods_money, id,
					pobilldetail_id
				from t_pw_bill_detail
				where pwbill_id = '%s' order by show_order";
		$items = $db->query($sql, $id);
		if (! $items) {
			return $this->bad("采购入库单没有采购明细记录，不能入库");
		}
		
		// 检查入库数量、单价、金额不能为负数
		foreach ( $items as $v ) {
			$goodsCount = intval($v["goods_count"]);
			if ($goodsCount <= 0) {
				$db->rollback();
				return $this->bad("采购数量不能小于1");
			}
			$goodsPrice = floatval($v["goods_price"]);
			if ($goodsPrice < 0) {
				$db->rollback();
				return $this->bad("采购单价不能为负数");
			}
			$goodsMoney = floatval($v["goods_money"]);
			if ($goodsMoney < 0) {
				$db->rollback();
				return $this->bad("采购金额不能为负数");
			}
		}
		
		$allPaymentType = array(
				0,
				1,
				2
		);
		if (! in_array($paymentType, $allPaymentType)) {
			return $this->bad("付款方式填写不正确，无法提交");
		}
		
		foreach ( $items as $v ) {
			$pwbilldetailId = $v["id"];
			
			$pobillDetailId = $v["pobilldetail_id"];
			
			$goodsCount = intval($v["goods_count"]);
			$goodsPrice = floatval($v["goods_price"]);
			$goodsMoney = floatval($v["goods_money"]);
			if ($goodsCount != 0) {
				$goodsPrice = $goodsMoney / $goodsCount;
			}
			
			$goodsId = $v["goods_id"];
			
			$balanceCount = 0;
			$balanceMoney = 0;
			$balancePrice = (float)0;
			// 库存总账
			$sql = "select in_count, in_money, balance_count, balance_money
					from t_inventory
					where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $warehouseId, $goodsId);
			if ($data) {
				$inCount = intval($data[0]["in_count"]);
				$inMoney = floatval($data[0]["in_money"]);
				$balanceCount = intval($data[0]["balance_count"]);
				$balanceMoney = floatval($data[0]["balance_money"]);
				
				$inCount += $goodsCount;
				$inMoney += $goodsMoney;
				$inPrice = $inMoney / $inCount;
				
				$balanceCount += $goodsCount;
				$balanceMoney += $goodsMoney;
				$balancePrice = $balanceMoney / $balanceCount;
				
				$sql = "update t_inventory
						set in_count = %d, in_price = %f, in_money = %f,
						balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
						$balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$inCount = $goodsCount;
				$inMoney = $goodsMoney;
				$inPrice = $inMoney / $inCount;
				$balanceCount += $goodsCount;
				$balanceMoney += $goodsMoney;
				$balancePrice = $balanceMoney / $balanceCount;
				
				$sql = "insert into t_inventory (in_count, in_price, in_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
						$balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
			
			// 库存明细账
			$sql = "insert into t_inventory_detail (in_count, in_price, in_money, balance_count,
					balance_price, balance_money, warehouse_id, goods_id, biz_date,
					biz_user_id, date_created, ref_number, ref_type)
					values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购入库')";
			$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $balanceCount, 
					$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, $ref);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 先进先出
			if ($fifo) {
				$dt = date("Y-m-d H:i:s");
				$sql = "insert into t_inventory_fifo (in_count, in_price, in_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id, date_created, in_ref,
						in_ref_type, pwbilldetail_id)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', '采购入库', '%s')";
				$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $goodsCount, 
						$goodsPrice, $goodsMoney, $warehouseId, $goodsId, $dt, $ref, $pwbilldetailId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				// fifo 明细记录
				$sql = "insert into t_inventory_fifo_detail(in_count, in_price, in_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id, date_created, pwbilldetail_id)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s')";
				$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $goodsCount, 
						$goodsPrice, $goodsMoney, $warehouseId, $goodsId, $dt, $pwbilldetailId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
			
			// 同步采购订单中的到货情况
			$sql = "select goods_count, pw_count
					from t_po_bill_detail
					where id = '%s' ";
			$poDetail = $db->query($sql, $pobillDetailId);
			if (! $poDetail) {
				// 当前采购入库单不是由采购订单创建的
				continue;
			}
			
			$totalGoodsCount = $poDetail[0]["goods_count"];
			$totalPWCount = $poDetail[0]["pw_count"];
			$totalPWCount += $goodsCount;
			$totalLeftCount = $totalGoodsCount - $totalPWCount;
			
			$sql = "update t_po_bill_detail
					set pw_count = %d, left_count = %d
					where id = '%s' ";
			$rc = $db->execute($sql, $totalPWCount, $totalLeftCount, $pobillDetailId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 修改本单据状态为已入库
		$sql = "update t_pw_bill set bill_status = 1000 where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 同步采购订单的状态
		$sql = "select po_id
				from t_po_pw
				where pw_id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$poBillId = $data[0]["po_id"];
			
			$sql = "select count(*) as cnt from t_po_bill_detail
					where pobill_id = '%s' and left_count > 0 ";
			$data = $db->query($sql, $poBillId);
			$cnt = $data[0]["cnt"];
			$billStatus = 1000;
			if ($cnt > 0) {
				// 部分入库
				$billStatus = 2000;
			} else {
				// 全部入库
				$billStatus = 3000;
			}
			$sql = "update t_po_bill
					set bill_status = %d
					where id = '%s' ";
			$rc = $db->execute($sql, $billStatus, $poBillId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		if ($paymentType == 0) {
			// 记应付账款
			// 应付明细账
			$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money,
					ca_id, ca_type, date_created, ref_number, ref_type, biz_date, company_id)
					values ('%s', %f, 0, %f, '%s', 'supplier', now(), '%s', '采购入库', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $billPayables, $billPayables, $supplierId, 
					$ref, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 应付总账
			$sql = "select id, pay_money
					from t_payables
					where ca_id = '%s' and ca_type = 'supplier' and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			if ($data) {
				$pId = $data[0]["id"];
				$payMoney = floatval($data[0]["pay_money"]);
				$payMoney += $billPayables;
				
				$sql = "update t_payables
						set pay_money = %f, balance_money = %f
						where id = '%s' ";
				$rc = $db->execute($sql, $payMoney, $payMoney, $pId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$payMoney = $billPayables;
				
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money,
						ca_id, ca_type, company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s')";
				$rc = $db->execute($sql, $this->newId(), $payMoney, $payMoney, $supplierId, 
						$companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		} else if ($paymentType == 1) {
			// 现金付款
			
			$outCash = $billPayables;
			
			$sql = "select in_money, out_money, balance_money
					from t_cash
					where biz_date = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $bizDT, $companyId);
			if (! $data) {
				// 当天首次发生现金业务
				
				$sql = "select sum(in_money) as sum_in_money, sum(out_money) as sum_out_money
							from t_cash
							where biz_date <= '%s' and company_id = '%s' ";
				$data = $db->query($sql, $bizDT, $companyId);
				$sumInMoney = $data[0]["sum_in_money"];
				$sumOutMoney = $data[0]["sum_out_money"];
				if (! $sumInMoney) {
					$sumInMoney = 0;
				}
				if (! $sumOutMoney) {
					$sumOutMoney = 0;
				}
				
				$balanceCash = $sumInMoney - $sumOutMoney - $outCash;
				$sql = "insert into t_cash(out_money, balance_money, biz_date, company_id)
							values (%f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			} else {
				$balanceCash = $data[0]["balance_money"] - $outCash;
				$sumOutMoney = $data[0]["out_money"] + $outCash;
				$sql = "update t_cash
							set out_money = %f, balance_money = %f
							where biz_date = '%s' and company_id = '%s' ";
				$rc = $db->execute($sql, $sumOutMoney, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
			
			// 调整业务日期之后的现金总账和明细账的余额
			$sql = "update t_cash
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			$sql = "update t_cash_detail
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else if ($paymentType == 2) {
			// 2: 预付款
			
			$outMoney = $billPayables;
			
			$sql = "select out_money, balance_money from t_pre_payment
						where supplier_id = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			$totalOutMoney = $data[0]["out_money"];
			$totalBalanceMoney = $data[0]["balance_money"];
			if (! $totalOutMoney) {
				$totalOutMoney = 0;
			}
			if (! $totalBalanceMoney) {
				$totalBalanceMoney = 0;
			}
			if ($outMoney > $totalBalanceMoney) {
				$supplierName = $supplier["name"];
				$info = "供应商[{$supplierName}]预付款余额不足，无法完成支付<br/><br/>余额:{$totalBalanceMoney}元，付款金额:{$outMoney}元";
				return $this->bad($info);
			}
			
			// 预付款总账
			$sql = "update t_pre_payment
					set out_money = %f, balance_money = %f
					where supplier_id = '%s' and company_id = '%s' ";
			$totalOutMoney += $outMoney;
			$totalBalanceMoney -= $outMoney;
			$rc = $db->execute($sql, $totalOutMoney, $totalBalanceMoney, $supplierId, $companyId);
			if (! $rc) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 预付款明细账
			$sql = "insert into t_pre_payment_detail(id, supplier_id, out_money, balance_money,
						biz_date, date_created, ref_number, ref_type, biz_user_id, input_user_id,
						company_id)
						values ('%s', '%s', %f, %f, '%s', now(), '%s', '采购入库', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $supplierId, $outMoney, $totalBalanceMoney, 
					$bizDT, $ref, $bizUserId, $loginUserId, $companyId);
			if (! $rc) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 同步库存账中的在途库存
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
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 查询采购入库单的数据，用于生成PDF文件
	 *
	 * @param array $params        	
	 *
	 * @return NULL|array
	 */
	public function getDataForPDF($params) {
		$db = $this->db;
		
		$ref = $params["ref"];
		
		$sql = "select p.id, p.bill_status, p.ref, p.biz_dt, u1.name as biz_user_name, u2.name as input_user_name,
					p.goods_money, w.name as warehouse_name, s.name as supplier_name,
					p.date_created, p.payment_type
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id)
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) 
				and (p.ref = '%s')";
		
		$data = $db->query($sql, $ref);
		if (! $data) {
			return null;
		}
		
		$v = $data[0];
		$id = $v["id"];
		
		$result = array();
		
		$result["billStatus"] = $v["bill_status"];
		$result["supplierName"] = $v["supplier_name"];
		$result["goodsMoney"] = $v["goods_money"];
		$result["bizDT"] = $this->toYMD($v["biz_dt"]);
		$result["warehouseName"] = $v["warehouse_name"];
		$result["bizUserName"] = $v["biz_user_name"];
		
		$sql = "select g.code, g.name, g.spec, u.name as unit_name, p.goods_count, p.goods_price,
					p.goods_money
				from t_pw_bill_detail p, t_goods g, t_goods_unit u
				where p.pwbill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order ";
		$items = array();
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$item = array(
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"goodsCount" => $v["goods_count"],
					"unitName" => $v["unit_name"],
					"goodsPrice" => $v["goods_price"],
					"goodsMoney" => $v["goods_money"]
			);
			
			$items[] = $item;
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	/**
	 * 通过单号查询采购入库的完整信息，包括明细入库记录
	 *
	 * @param string $ref
	 *        	采购入库单单号
	 * @return array|NULL
	 */
	public function getFullBillDataByRef($ref) {
		$db = $this->db;
		
		$sql = "select p.id, s.name as supplier_name,
					w.name as  warehouse_name,
					u.name as biz_user_name, p.biz_dt
				from t_pw_bill p, t_supplier s, t_warehouse w, t_user u
				where p.ref = '%s' and p.supplier_id = s.id and p.warehouse_id = w.id
				  and p.biz_user_id = u.id";
		$data = $db->query($sql, $ref);
		if (! $data) {
			return NULL;
		}
		
		$v = $data[0];
		$id = $v["id"];
		
		$result = array(
				"supplierName" => $v["supplier_name"],
				"warehouseName" => $v["warehouse_name"],
				"bizUserName" => $v["biz_user_name"],
				"bizDT" => $this->toYMD($v["biz_dt"])
		
		);
		
		// 明细记录
		$items = array();
		$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name,
					p.goods_count, p.goods_price, p.goods_money, p.memo
					from t_pw_bill_detail p, t_goods g, t_goods_unit u
					where p.goods_Id = g.id and g.unit_id = u.id and p.pwbill_id = '%s'
					order by p.show_order";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$item = array(
					"id" => $v["id"],
					"goodsId" => $v["goods_id"],
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"unitName" => $v["unit_name"],
					"goodsCount" => $v["goods_count"],
					"goodsPrice" => $v["goods_price"],
					"goodsMoney" => $v["goods_money"],
					"memo" => $v["memo"]
			
			);
			$items[] = $item;
		}
		
		$result["items"] = $items;
		
		return $result;
	}
}