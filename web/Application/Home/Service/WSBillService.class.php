<?php

namespace Home\Service;

/**
 * 销售出库Service
 *
 * @author 李静波
 */
class WSBillService extends PSIBaseService {
	public function wsBillInfo($params) {
		$id = $params["id"];
		$us = new UserService();
		$result = array();
		$result["canEditGoodsPrice"] = $this->canEditGoodsPrice();
		
		if (! $id) {
			// 新建销售出库单
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			
			$ts = new BizConfigService();
			if ($ts->warehouseUsesOrg()) {
				$ws = new WarehouseService();
				$data = $ws->getWarehouseListForLoginUser("2002");
				if (count($data) > 0) {
					$result["warehouseId"] = $data[0]["id"];
					$result["warehouseName"] = $data[0]["name"];
				}
			} else {
				$db = M();
				$sql = "select value from t_config where id = '2002-02' ";
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
			return $result;
		} else {
			$db = M();
			$sql = "select w.id, w.ref, w.bizdt, c.id as customer_id, c.name as customer_name, " . " u.id as biz_user_id, u.name as biz_user_name," . " h.id as warehouse_id, h.name as warehouse_name " . " from t_ws_bill w, t_customer c, t_user u, t_warehouse h " . " where w.customer_id = c.id and w.biz_user_id = u.id " . " and w.warehouse_id = h.id " . " and w.id = '%s' ";
			$data = $db->query($sql, $id);
			if ($data) {
				$result["ref"] = $data[0]["ref"];
				$result["bizDT"] = date("Y-m-d", strtotime($data[0]["bizdt"]));
				$result["customerId"] = $data[0]["customer_id"];
				$result["customerName"] = $data[0]["customer_name"];
				$result["warehouseId"] = $data[0]["warehouse_id"];
				$result["warehouseName"] = $data[0]["warehouse_name"];
				$result["bizUserId"] = $data[0]["biz_user_id"];
				$result["bizUserName"] = $data[0]["biz_user_name"];
			}
			
			$sql = "select d.id, g.id as goods_id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count, " . "d.goods_price, d.goods_money " . " from t_ws_bill_detail d, t_goods g, t_goods_unit u " . " where d.wsbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id" . " order by d.show_order";
			$data = $db->query($sql, $id);
			$items = array();
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
			
			return $result;
		}
	}
	private function canEditGoodsPrice() {
		$db = M();
		$sql = "select value from t_config where id = '2002-01' ";
		$data = $db->query($sql);
		if (! $data) {
			return false;
		}
		
		$v = intval($data[0]["value"]);
		if ($v == 0) {
			return false;
		}
		
		$us = new UserService();
		
		return $us->hasPermission("2002-01");
	}
	public function editWSBill($params) {
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		$bizDT = $bill["bizDT"];
		$warehouseId = $bill["warehouseId"];
		$customerId = $bill["customerId"];
		$bizUserId = $bill["bizUserId"];
		$items = $bill["items"];
		
		$db = M();
		
		$idGen = new IdGenService();
		if ($id) {
			// 编辑
			$sql = "select ref, bill_status from t_ws_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				return $this->bad("要编辑的销售出库单不存在");
			}
			$ref = $data[0]["ref"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				return $this->bad("销售出库单[单号：{$ref}]已经提交出库了，不能再编辑");
			}
			
			$db->startTrans();
			try {
				$sql = "delete from t_ws_bill_detail where wsbill_id = '%s' ";
				$db->execute($sql, $id);
				$sql = "insert into t_ws_bill_detail (id, date_created, goods_id, " . " goods_count, goods_price, goods_money," . " show_order, wsbill_id) values ('%s', now(), '%s', %d, %f, %f, %d, '%s')";
				foreach ( $items as $i => $v ) {
					$goodsId = $v["goodsId"];
					if ($goodsId) {
						$goodsCount = intval($v["goodsCount"]);
						$goodsPrice = floatval($v["goodsPrice"]);
						$goodsMoney = $goodsCount * $goodsPrice;
						
						$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, $goodsMoney, $i, $id);
					}
				}
				$sql = "select sum(goods_money) as sum_goods_money from t_ws_bill_detail where wsbill_id = '%s' ";
				$data = $db->query($sql, $id);
				$sumGoodsMoney = $data[0]["sum_goods_money"];
				
				$sql = "update t_ws_bill " . " set sale_money = %f, customer_id = '%s', warehouse_id = '%s', " . " biz_user_id = '%s', bizdt = '%s' " . " where id = '%s' ";
				$db->execute($sql, $sumGoodsMoney, $customerId, $warehouseId, $bizUserId, $bizDT, $id);
				
				$log = "编辑销售出库单，单号 = {$ref}";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "销售出库");
				
				$db->commit();
			} catch ( Exception $ex ) {
				$db->rollback();
				return $this->bad("数据库错误，请联系管理员");
			}
		} else {
			// 新增
			$db->startTrans();
			try {
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				$sql = "insert into t_ws_bill(id, bill_status, bizdt, biz_user_id, customer_id,  date_created," . " input_user_id, ref, warehouse_id) " . " values ('%s', 0, '%s', '%s', '%s', now(), '%s', '%s', '%s')";
				$us = new UserService();
				$db->execute($sql, $id, $bizDT, $bizUserId, $customerId, $us->getLoginUserId(), $ref, $warehouseId);
				
				$sql = "insert into t_ws_bill_detail (id, date_created, goods_id, " . " goods_count, goods_price, goods_money," . " show_order, wsbill_id) values ('%s', now(), '%s', %d, %f, %f, %d, '%s')";
				foreach ( $items as $i => $v ) {
					$goodsId = $v["goodsId"];
					if ($goodsId) {
						$goodsCount = intval($v["goodsCount"]);
						$goodsPrice = floatval($v["goodsPrice"]);
						$goodsMoney = $goodsCount * $goodsPrice;
						
						$db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsPrice, $goodsMoney, $i, $id);
					}
				}
				$sql = "select sum(goods_money) as sum_goods_money from t_ws_bill_detail where wsbill_id = '%s' ";
				$data = $db->query($sql, $id);
				$sumGoodsMoney = $data[0]["sum_goods_money"];
				
				$sql = "update t_ws_bill set sale_money = %f where id = '%s' ";
				$db->execute($sql, $sumGoodsMoney, $id);
				
				$log = "新增销售出库单，单号 = {$ref}";
				$bs = new BizlogService();
				$bs->insertBizlog($log, "销售出库");
				
				$db->commit();
			} catch ( Exception $ex ) {
				$db->rollback();
				return $this->bad("数据库错误，请联系管理员");
			}
		}
		
		return $this->ok($id);
	}
	private function genNewBillRef() {
		$pre = "WS";
		$mid = date("Ymd");
		
		$sql = "select ref from t_ws_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
		$sufLength = 3;
		$suf = str_pad("1", $sufLength, "0", STR_PAD_LEFT);
		if ($data) {
			$ref = $data[0]["ref"];
			$nextNumber = intval(substr($ref, 10)) + 1;
			$suf = str_pad($nextNumber, $sufLength, "0", STR_PAD_LEFT);
		}
		
		return $pre . $mid . $suf;
	}
	public function wsbillList($params) {
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$sql = "select w.id, w.ref, w.bizdt, c.name as customer_name, u.name as biz_user_name," . " user.name as input_user_name, h.name as warehouse_name, w.sale_money," . " w.bill_status " . " from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h " . " where w.customer_id = c.id and w.biz_user_id = u.id " . " and w.input_user_id = user.id and w.warehouse_id = h.id " . " order by w.ref desc " . " limit " . $start . ", " . $limit;
		$data = $db->query($sql);
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
		}
		
		$sql = "select count(*) as cnt " . " from t_ws_bill w, t_customer c, t_user u, t_user user, t_warehouse h " . " where w.customer_id = c.id and w.biz_user_id = u.id " . " and w.input_user_id = user.id and w.warehouse_id = h.id ";
		$data = $db->query($sql);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
	public function wsBillDetailList($params) {
		$billId = $params["billId"];
		$sql = "select d.id, g.code, g.name, g.spec, u.name as unit_name, d.goods_count, " . "d.goods_price, d.goods_money " . " from t_ws_bill_detail d, t_goods g, t_goods_unit u " . " where d.wsbill_id = '%s' and d.goods_id = g.id and g.unit_id = u.id" . " order by d.show_order";
		$data = M()->query($sql, $billId);
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
		}
		
		return $result;
	}
	public function deleteWSBill($params) {
		$id = $params["id"];
		$db = M();
		$sql = "select ref, bill_status from t_ws_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的销售出库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			return $this->bad("销售出库单已经提交出库，不能删除");
		}
		
		$db->startTrans();
		try {
			$sql = "delete from t_ws_bill_detail where wsbill_id = '%s' ";
			$db->execute($sql, $id);
			$sql = "delete from t_ws_bill where id = '%s' ";
			$db->execute($sql, $id);
			
			$log = "删除销售出库单，单号: {$ref}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "销售出库");
			$db->commit();
		} catch ( Exception $ex ) {
			$db->rollback();
			return $this->bad("数据库错误，请联系管理员");
		}
		
		return $this->ok();
	}
	public function commitWSBill($params) {
		$id = $params["id"];
		
		$db = M();
		$sql = "select ref, bill_status, customer_id, warehouse_id, biz_user_id, bizdt, sale_money " . " from t_ws_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! data) {
			return $this->bad("要提交的销售出库单不存在");
		}
		$ref = $data[0]["ref"];
		$bizDT = $data[0]["bizdt"];
		$bizUserId = $data[0]["biz_user_id"];
		$billStatus = $data[0]["bill_status"];
		$saleMoney = $data[0]["sale_money"];
		if ($billStatus != 0) {
			return $this->bad("销售出库单已经提交出库，不能再次提交");
		}
		$customerId = $data[0]["customer_id"];
		$warehouseId = $data[0]["warehouse_id"];
		$sql = "select count(*) as cnt from t_customer where id = '%s' ";
		$data = $db->query($sql, $customerId);
		$cnt = $data[0]["cnt"];
		if ($cnt != 1) {
			return $this->bad("客户不存在");
		}
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			return $this->bad("仓库不存在");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			return $this->bad("仓库 [{$warehouseName}]还没有建账，不能进行出库操作");
		}
		$sql = "select name as cnt from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			return $this->bad("业务员不存在");
		}
		
		$db->startTrans();
		try {
			$sql = "select id, goods_id, goods_count,  goods_price " . " from t_ws_bill_detail " . " where wsbill_id = '%s' " . " order by show_order ";
			$items = $db->query($sql, $id);
			if (! $items) {
				$db->rollback();
				return $this->bad("采购出库单没有出库商品明细记录，无法出库");
			}
			
			foreach ( $items as $v ) {
				$itemId = $v["id"];
				$goodsId = $v["goods_id"];
				$goodsCount = intval($v["goods_count"]);
				$goodsPrice = floatval($v["goods_price"]);
				
				$sql = "select code, name from t_goods where id = '%s' ";
				$data = $db->query($sql, $goodsId);
				if (! $data) {
					$db->rollback();
					return $this->bad("要出库的商品不存在(商品后台id = {$goodsId})");
				}
				$goodsCode = $data[0]["code"];
				$goodsName = $data[0]["name"];
				if ($goodsCount <= 0) {
					$db->rollback();
					return $this->bad("商品[{$goodsCode} {$goodsName}]的出库数量需要是正数");
				}
				
				// 库存总账
				$sql = "select out_count, out_money, balance_count, balance_price," . " balance_money from t_inventory " . " where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$db->rollback();
					return $this->bad("商品 [{$goodsCode} {$goodsName}] 在仓库 [{$warehouseName}] 中没有存货，无法出库");
				}
				$balanceCount = $data[0]["balance_count"];
				if ($balanceCount < $goodsCount) {
					$db->rollback();
					return $this->bad("商品 [{$goodsCode} {$goodsName}] 在仓库 [{$warehouseName}] 中存货数量不足，无法出库");
				}
				$balancePrice = $data[0]["balance_price"];
				$balanceMoney = $data[0]["balance_money"];
				$outCount = $data[0]["out_count"];
				$outMoney = $data[0]["out_money"];
				$balanceCount -= $goodsCount;
				if ($balanceCount == 0) {
					// 当全部出库的时候，金额也需要全部转出去
					$outMoney += $balanceMoney;
					$outPriceDetail = $balanceMoney / $goodsCount;
					$outMoneyDetail = $balanceMoney;
					$balanceMoney = 0;
				} else {
					$outMoney += $goodsCount * $balancePrice;
					$outPriceDetail = $balancePrice;
					$outMoneyDetail = $goodsCount * $balancePrice;
					$balanceMoney -= $goodsCount * $balancePrice;
				}
				$outCount += $goodsCount;
				$outPrice = $outMoney / $outCount;
				
				$sql = "update t_inventory " . " set out_count = %d, out_price = %f, out_money = %f," . "       balance_count = %d, balance_money = %f " . " where warehouse_id = '%s' and goods_id = '%s' ";
				$db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, $balanceMoney, $warehouseId, $goodsId);
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(out_count, out_price, out_money, " . " balance_count, balance_price, balance_money, warehouse_id," . " goods_id, biz_date, biz_user_id, date_created, ref_number, ref_type) " . " values(%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '销售出库')";
				$db->execute($sql, $goodsCount, $outPriceDetail, $outMoneyDetail, $balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, $ref);
				
				// 单据本身的记录
				$sql = "update t_ws_bill_detail " . " set inventory_price = %f, inventory_money = %f" . " where id = '%s' ";
				$db->execute($sql, $outPriceDetail, $outMoneyDetail, $itemId);
			}
			
			// 应收总账
			$sql = "select rv_money, balance_money " . " from t_receivables " . " where ca_id = '%s' and ca_type = 'customer' ";
			$data = $db->query($sql, $customerId);
			if ($data) {
				$rvMoney = $data[0]["rv_money"];
				$balanceMoney = $data[0]["balance_money"];
				
				$rvMoney += $saleMoney;
				$balanceMoney += $saleMoney;
				
				$sql = "update t_receivables" . " set rv_money = %f,  balance_money = %f " . " where ca_id = '%s' and ca_type = 'customer' ";
				$db->execute($sql, $rvMoney, $balanceMoney, $customerId);
			} else {
				$sql = "insert into t_receivables (id, rv_money, act_money, balance_money," . " ca_id, ca_type) values ('%s', %f, 0, %f, '%s', 'customer')";
				$idGen = new IdGenService();
				$db->execute($sql, $idGen->newId(), $saleMoney, $saleMoney, $customerId);
			}
			
			// 应收明细账
			$sql = "insert into t_receivables_detail (id, rv_money, act_money, balance_money," . " ca_id, ca_type, date_created, ref_number, ref_type, biz_date) " . " values('%s', %f, 0, %f, '%s', 'customer', now(), '%s', '销售出库', '%s')";
			$idGen = new IdGenService();
			$db->execute($sql, $idGen->newId(), $saleMoney, $saleMoney, $customerId, $ref, $bizDT);
			
			// 单据本身设置为已经提交出库
			$sql = "select sum(inventory_money) as sum_inventory_money " . " from t_ws_bill_detail " . " where wsbill_id = '%s' ";
			$data = $db->query($sql, $id);
			$sumInventoryMoney = $data[0]["sum_inventory_money"];
			
			$profit = $saleMoney - $sumInventoryMoney;
			
			$sql = "update t_ws_bill " . " set bill_status = 1000, inventory_money = %f, profit = %f " . " where id = '%s' ";
			$db->execute($sql, $sumInventoryMoney, $profit, $id);
			
			$log = "提交销售出库单，单号 = {$ref}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "销售出库");
			$db->commit();
		} catch ( Exception $ex ) {
			$db->rollback();
			return $this->bad("数据库错误，请联系管理员");
		}
		
		return $this->ok($id);
	}
}
