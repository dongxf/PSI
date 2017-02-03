<?php

namespace Home\Service;

use Home\DAO\PWBillDAO;

/**
 * 采购入库Service
 *
 * @author 李静波
 */
class PWBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购入库";

	/**
	 * 获得采购入库单主表列表
	 */
	public function pwbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new PWBillDAO();
		return $dao->pwbillList($params);
	}

	/**
	 * 获得采购入库单商品明细记录列表
	 */
	public function pwBillDetailList($pwbillId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"id" => $pwbillId
		);
		
		$dao = new PWBillDAO();
		return $dao->pwBillDetailList($params);
	}

	/**
	 * 新建或编辑采购入库单
	 */
	public function editPWBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new PWBillDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑采购入库单
			
			$rc = $dao->updatePWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑采购入库单: 单号 = {$ref}";
		} else {
			// 新建采购入库单
			
			$us = new UserService();
			$bill["companyId"] = $us->getCompanyId();
			$bill["loginUserId"] = $us->getLoginUserId();
			$bill["dataOrg"] = $us->getLoginUserDataOrg();
			
			$rc = $dao->addPWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$pobillRef = $bill["pobillRef"];
			if ($pobillRef) {
				// 从采购订单生成采购入库单
				$log = "从采购订单(单号：{$pobillRef})生成采购入库单: 单号 = {$ref}";
			} else {
				// 手工新建采购入库单
				$log = "新建采购入库单: 单号 = {$ref}";
			}
		}
		
		// 同步库存账中的在途库存
		$rc = $dao->updateAfloatInventoryByPWBill($bill);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个采购入库单的信息
	 */
	public function pwBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		$params["loginUserName"] = $us->getLoginUserName();
		$params["companyId"] = $us->getCompanyId();
		
		$dao = new PWBillDAO();
		return $dao->pwBillInfo($params);
	}

	/**
	 * 删除采购入库单
	 */
	public function deletePWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, bill_status, warehouse_id from t_pw_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的采购入库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("当前采购入库单已经提交入库，不能删除");
		}
		$warehouseId = $data[0]["warehouse_id"];
		
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
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_pw_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 删除从采购订单生成的记录
		$sql = "delete from t_po_pw where pw_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 同步库存账中的在途库存
		foreach ( $goodsIdList as $v ) {
			$goodsId = $v;
			
			$rc = $this->updateAfloatInventory($db, $warehouseId, $goodsId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 记录业务日志
		$log = "删除采购入库单: 单号 = {$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购入库单
	 */
	public function commitPWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bs = new BizConfigService();
		
		// true: 先进先出法
		$fifo = $bs->getInventoryMethod() == 1;
		
		$db = M();
		$db->startTrans();
		
		$sql = "select ref, warehouse_id, bill_status, biz_dt, biz_user_id,  goods_money, supplier_id,
					payment_type, company_id
				from t_pw_bill 
				where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的采购入库单不存在");
		}
		$billStatus = $data[0]["bill_status"];
		if ($billStatus != 0) {
			$db->rollback();
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
		
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("要入库的仓库不存在");
		}
		$inited = $data[0]["inited"];
		if ($inited == 0) {
			$db->rollback();
			return $this->bad("仓库 [{$data[0]['name']}] 还没有完成建账，不能做采购入库的操作");
		}
		
		// 检查供应商是否存在
		$ss = new SupplierService();
		if (! $ss->supplierExists($supplierId, $db)) {
			$db->rollback();
			return $this->bad("供应商不存在");
		}
		
		// 检查业务员是否存在
		$us = new UserService();
		if (! $us->userExists($bizUserId, $db)) {
			$db->rollback();
			return $this->bad("业务员不存在");
		}
		
		$sql = "select goods_id, goods_count, goods_price, goods_money, id 
				from t_pw_bill_detail 
				where pwbill_id = '%s' order by show_order";
		$items = $db->query($sql, $id);
		if (! $items) {
			$db->rollback();
			return $this->bad("采购入库单没有采购明细记录，不能入库");
		}
		
		// 检查入库数量、单价、金额不能为负数
		foreach ( $items as $v ) {
			$goodsCount = intval($v["goods_count"]);
			if ($goodsCount <= 0) {
				$db->rollback();
				return $this->bad("采购数量不能小于0");
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
			$db->rollback();
			return $this->bad("付款方式填写不正确，无法提交");
		}
		
		foreach ( $items as $v ) {
			$pwbilldetailId = $v["id"];
			
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
					$db->rollback();
					return $this->sqlError(__LINE__);
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
					$db->rollback();
					return $this->sqlError(__LINE__);
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
				$db->rollback();
				return $this->sqlError(__LINE__);
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
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// fifo 明细记录
				$sql = "insert into t_inventory_fifo_detail(in_count, in_price, in_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, date_created, pwbilldetail_id)
							values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s')";
				$rc = $db->execute($sql, $goodsCount, $goodsPrice, $goodsMoney, $goodsCount, 
						$goodsPrice, $goodsMoney, $warehouseId, $goodsId, $dt, $pwbilldetailId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
		}
		
		$sql = "update t_pw_bill set bill_status = 1000 where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		if ($paymentType == 0) {
			// 记应付账款
			// 应付明细账
			$sql = "insert into t_payables_detail (id, pay_money, act_money, balance_money,
					ca_id, ca_type, date_created, ref_number, ref_type, biz_date, company_id)
					values ('%s', %f, 0, %f, '%s', 'supplier', now(), '%s', '采购入库', '%s', '%s')";
			$idGen = new IdGenService();
			$rc = $db->execute($sql, $idGen->newId(), $billPayables, $billPayables, $supplierId, 
					$ref, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
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
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$payMoney = $billPayables;
				
				$sql = "insert into t_payables (id, pay_money, act_money, balance_money, 
						ca_id, ca_type, company_id) 
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $payMoney, $payMoney, $supplierId, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
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
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type, 
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCash = $data[0]["balance_money"] - $outCash;
				$sumOutMoney = $data[0]["out_money"] + $outCash;
				$sql = "update t_cash
							set out_money = %f, balance_money = %f
							where biz_date = '%s' and company_id = '%s' ";
				$rc = $db->execute($sql, $sumOutMoney, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(out_money, balance_money, biz_date, ref_type, 
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购入库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $outCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 调整业务日期之后的现金总账和明细账的余额
			$sql = "update t_cash
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "update t_cash_detail
							set balance_money = balance_money - %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $outCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
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
				$db->rollback();
				$ss = new SupplierService();
				$supplierName = $ss->getSupplierNameById($supplierId, $db);
				return $this->bad(
						"供应商[{$supplierName}]预付款余额不足，无法完成支付<br/><br/>余额:{$totalBalanceMoney}元，付款金额:{$outMoney}元");
			}
			
			// 预付款总账
			$sql = "update t_pre_payment
						set out_money = %f, balance_money = %f
						where supplier_id = '%s' and company_id = '%s' ";
			$totalOutMoney += $outMoney;
			$totalBalanceMoney -= $outMoney;
			$rc = $db->execute($sql, $totalOutMoney, $totalBalanceMoney, $supplierId, $companyId);
			if (! $rc) {
				$db->rollback();
				return $this->sqlError();
			}
			
			// 预付款明细账
			$sql = "insert into t_pre_payment_detail(id, supplier_id, out_money, balance_money,
						biz_date, date_created, ref_number, ref_type, biz_user_id, input_user_id,
						company_id)
						values ('%s', '%s', %f, %f, '%s', now(), '%s', '采购入库', '%s', '%s', '%s')";
			$idGen = new IdGenService();
			$us = new UserService();
			$rc = $db->execute($sql, $idGen->newId(), $supplierId, $outMoney, $totalBalanceMoney, 
					$bizDT, $ref, $bizUserId, $us->getLoginUserId(), $companyId);
			if (! $rc) {
				$db->rollback();
				return $this->sqlError();
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
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 业务日志
		$log = "提交采购入库单: 单号 = {$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 同步在途库存
	 */
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
}