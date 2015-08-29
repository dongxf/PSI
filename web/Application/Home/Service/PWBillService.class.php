<?php

namespace Home\Service;

/**
 * 采购入库Service
 *
 * @author 李静波
 */
class PWBillService extends PSIBaseService {

	public function pwbillList($params) {
		if ($this->isNotOnline()) {
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
		$supplierId = $params["supplierId"];
		
		$db = M();
		
		$queryParams = array();
		$sql = "select p.id, p.bill_status, p.ref, p.biz_dt, u1.name as biz_user_name, u2.name as input_user_name, 
					p.goods_money, w.name as warehouse_name, s.name as supplier_name,
					p.date_created, p.payment_type
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2 
				where (p.warehouse_id = w.id) and (p.supplier_id = s.id) 
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) ";
		
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
		
		$sql .= " order by p.ref desc 
				limit %d, %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["bizDate"] = date("Y-m-d", strtotime($v["biz_dt"]));
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
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function pwBillDetailList($pwbillId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select p.id, g.code, g.name, g.spec, u.name as unit_name, p.goods_count, p.goods_price, 
					p.goods_money 
				from t_pw_bill_detail p, t_goods g, t_goods_unit u 
				where p.pwbill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id 
				order by p.show_order ";
		$data = M()->query($sql, $pwbillId);
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
		}
		
		return $result;
	}

	public function editPWBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$supplierId = $bill["supplierId"];
		$bizUserId = $bill["bizUserId"];
		
		$db = M();
		
		$sql = "select count(*) as cnt from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("入库仓库不存在");
		}
		
		$sql = "select count(*) as cnt from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("供应商不存在");
		}
		
		$sql = "select count(*) as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->bad("业务人员不存在");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		$idGen = new IdGenService();
		
		if ($id) {
			// 编辑
			$sql = "select ref, bill_status from t_pw_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $this->bad("要编辑的采购入库单不存在");
			}
			$billStatus = $data[0]["bill_status"];
			$ref = $data[0]["ref"];
			if ($billStatus != 0) {
				return $this->bad("当前采购入库单已经提交入库，不能再编辑");
			}
			
			$db->startTrans();
			try {
				$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
				$db->execute($sql, $id);
				
				// 明细记录
				$items = $bill["items"];
				foreach ( $items as $i => $item ) {
					$goodsId = $item["goodsId"];
					$goodsCount = intval($item["goodsCount"]);
					if ($goodsId != null && $goodsCount != 0) {
						// 检查商品是否存在
						$sql = "select count(*) as cnt from t_goods where id = '%s' ";
						$data = $db->query($sql, $goodsId);
						$cnt = $data[0]["cnt"];
						if ($cnt == 1) {
							
							$goodsPrice = $item["goodsPrice"];
							$goodsMoney = $item["goodsMoney"];
							
							$sql = "insert into t_pw_bill_detail (id, date_created, goods_id, goods_count, goods_price,
									goods_money,  pwbill_id, show_order)
									values ('%s', now(), '%s', %d, %f, %f, '%s', %d )";
							$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, 
									$goodsMoney, $id, $i);
						}
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
							biz_user_id = '%s'
						where id = '%s' ";
				$db->execute($sql, $totalMoney, $warehouseId, $supplierId, $bizDT, $bizUserId, $id);
				
				$log = "编辑采购入库单: 单号 = {$ref}";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "采购入库");
				$db->commit();
			} catch ( Exception $exc ) {
				$db->rollback();
				return $this->bad("数据库操作错误，请联系管理员");
			}
		} else {
			$id = $idGen->newId();
			
			$db->startTrans();
			try {
				$sql = "insert into t_pw_bill (id, ref, supplier_id, warehouse_id, biz_dt, 
						biz_user_id, bill_status, date_created, goods_money, input_user_id) 
						values ('%s', '%s', '%s', '%s', '%s', '%s', 0, now(), 0, '%s')";
				
				$ref = $this->genNewBillRef();
				$us = new UserService();
				$db->execute($sql, $id, $ref, $supplierId, $warehouseId, $bizDT, $bizUserId, 
						$us->getLoginUserId());
				
				// 明细记录
				$items = $bill["items"];
				foreach ( $items as $i => $item ) {
					$goodsId = $item["goodsId"];
					$goodsCount = intval($item["goodsCount"]);
					if ($goodsId != null && $goodsCount != 0) {
						// 检查商品是否存在
						$sql = "select count(*) as cnt from t_goods where id = '%s' ";
						$data = $db->query($sql, $goodsId);
						$cnt = $data[0]["cnt"];
						if ($cnt == 1) {
							
							$goodsPrice = $item["goodsPrice"];
							$goodsMoney = $item["goodsMoney"];
							
							$sql = "insert into t_pw_bill_detail 
									(id, date_created, goods_id, goods_count, goods_price,
									goods_money,  pwbill_id, show_order)
									values ('%s', now(), '%s', %d, %f, %f, '%s', %d )";
							$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, 
									$goodsMoney, $id, $i);
						}
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
				$db->execute($sql, $totalMoney, $id);
				
				$log = "新建采购入库单: 单号 = {$ref}";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "采购入库");
				
				$db->commit();
			} catch ( Exception $exc ) {
				$db->rollback();
				
				return $this->bad("数据库操作错误，请联系管理员");
			}
		}
		
		return $this->ok($id);
	}

	private function genNewBillRef() {
		$pre = "PW";
		$mid = date("Ymd");
		
		$sql = "select ref from t_pw_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$suf = "001";
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, 3, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}

	public function pwBillInfo($id) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$result["id"] = $id;
		
		$db = M();
		$sql = "select p.ref, p.bill_status, p.supplier_id, s.name as supplier_name, 
				p.warehouse_id, w.name as  warehouse_name, 
				p.biz_user_id, u.name as biz_user_name, p.biz_dt 
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
			
			$items = array();
			$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, u.name as unit_name, 
					p.goods_count, p.goods_price, p.goods_money 
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
			}
			
			$result["items"] = $items;
		} else {
			// 新建采购入库单
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			
			$ts = new BizConfigService();
			if ($ts->warehouseUsesOrg()) {
				$ws = new WarehouseService();
				$data = $ws->getWarehouseListForLoginUser("2001");
				if (count($data) > 0) {
					$result["warehouseId"] = $data[0]["id"];
					$result["warehouseName"] = $data[0]["name"];
				}
			} else {
				$sql = "select value from t_config where id = '2001-01' ";
				$data = $db->query($sql);
				if ($data) {
					$warehouseId = $data[0]["value"];
					$sql = "select id, name from t_warehouse where id = '%s' ";
					$data = $db->query($sql, $warehouseId);
					if ($data) {
						$result["warehouseId"] = $data[0]["id"];
						$result["warehouseName"] = $data[0]["name"];
					}
				}
			}
		}
		
		return $result;
	}

	public function deletePWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$sql = "select ref, bill_status from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的采购入库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			return $this->bad("当前采购入库单已经提交入库，不能删除");
		}
		
		$db->startTrans();
		try {
			$sql = "delete from t_pw_bill_detail where pwbill_id = '%s' ";
			$db->execute($sql, $id);
			
			$sql = "delete from t_pw_bill where id = '%s' ";
			$db->execute($sql, $id);
			
			$log = "删除采购入库单: 单号 = {$ref}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "采购入库");
			
			$db->commit();
		} catch ( Exception $exc ) {
			$db->rollback();
			return $this->bad("数据库错误，请联系管理员");
		}
		
		return $this->ok();
	}

	public function commitPWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$sql = "select ref, warehouse_id, bill_status, biz_dt, biz_user_id,  goods_money, supplier_id 
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
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("要入库的仓库不存在");
		}
		$inited = $data[0]["inited"];
		if ($inited == 0) {
			return $this->bad("仓库 [{$data[0]['name']}] 还没有完成建账，不能做采购入库的操作");
		}
		
		$sql = "select goods_id, goods_count, goods_price, goods_money 
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
				return $this->bad("采购数量不能小于0");
			}
			$goodsPrice = floatval($v["goods_price"]);
			if ($goodsPrice < 0) {
				return $this->bad("采购单价不能为负数");
			}
			$goodsMoney = floatval($v["goods_money"]);
			if ($goodsMoney < 0) {
				return $this->bad("采购金额不能为负数");
			}
		}
		
		$db->startTrans();
		try {
			foreach ( $items as $v ) {
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
					$db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
							$balanceMoney, $warehouseId, $goodsId);
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
					$db->execute($sql, $inCount, $inPrice, $inMoney, $balanceCount, $balancePrice, 
							$balanceMoney, $warehouseId, $goodsId);
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail (in_count, in_price, in_money, balance_count,
						balance_price, balance_money, warehouse_id, goods_id, biz_date,
						biz_user_id, date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购入库')";
				$db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
						$ref);
			}
			
			$sql = "update t_pw_bill set bill_status = 1000 where id = '%s' ";
			$db->execute($sql, $id);
			
			// 应付明细账
			$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money,
					ca_id, ca_type, date_created, ref_number, ref_type, biz_date)
					values ('%s', %f, 0, %f, '%s', 'supplier', now(), '%s', '采购入库', '%s')";
			$idGen = new IdGenService();
			$db->execute($sql, $idGen->newId(), $billPayables, $billPayables, $supplierId, $ref, 
					$bizDT);
			// 应付总账
			$sql = "select id, pay_money 
					from t_payables 
					where ca_id = '%s' and ca_type = 'supplier' ";
			$data = $db->query($sql, $supplierId);
			if ($data) {
				$pId = $data[0]["id"];
				$payMoney = floatval($data[0]["pay_money"]);
				$payMoney += $billPayables;
				
				$sql = "update t_payables 
						set pay_money = %f, balance_money = %f 
						where id = '%s' ";
				$db->execute($sql, $payMoney, $payMoney, $pId);
			} else {
				$payMoney = $billPayables;
				
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, 
						ca_id, ca_type) 
						values ('%s', %f, 0, %f, '%s', 'supplier')";
				$db->execute($sql, $idGen->newId(), $payMoney, $payMoney, $supplierId);
			}
			
			// 日志
			$log = "提交采购入库单: 单号 = {$ref}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "采购入库");
			
			$db->commit();
		} catch ( Exception $exc ) {
			$db->rollback();
			return $this->bad("数据库操作错误，请联系管理员");
		}
		
		return $this->ok($id);
	}
}