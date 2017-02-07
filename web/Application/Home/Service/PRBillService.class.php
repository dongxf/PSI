<?php

namespace Home\Service;

use Home\DAO\PRBillDAO;

/**
 * 采购退货出库单Service
 *
 * @author 李静波
 */
class PRBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "采购退货出库";

	public function prBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new PRBillDAO();
		return $dao->prBillInfo($params);
	}

	/**
	 * 新建或编辑采购退货出库单
	 */
	public function editPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		$db->startTrans();
		
		$dao = new PRBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑采购退货出库单
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updatePRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "编辑采购退货出库单，单号：$ref";
		} else {
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->addPRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建采购退货出库单，单号：$ref";
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 选择可以退货的采购入库单
	 */
	public function selectPWBillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new PRBillDAO();
		return $dao->selectPWBillList($params);
	}

	/**
	 * 查询采购入库单的详细信息
	 */
	public function getPWBillInfoForPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PRBillDAO();
		return $dao->getPWBillInfoForPRBill($params);
	}

	/**
	 * 采购退货出库单列表
	 */
	public function prbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new PRBillDAO();
		return $dao->prbillList($params);
	}

	/**
	 * 采购退货出库单明细列表
	 */
	public function prBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PRBillDAO();
		return $dao->prBillDetailList($params);
	}

	/**
	 * 删除采购退货出库单
	 */
	public function deletePRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new PRBillDAO($db);
		$rc = $dao->deletePRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$bs = new BizlogService();
		$log = "删除采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购退货出库单
	 */
	public function commitPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bs = new BizConfigService();
		$fifo = $bs->getInventoryMethod() == 1;
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status, warehouse_id, bizdt, biz_user_id, rejection_money,
						supplier_id, receiving_type, company_id
					from t_pr_bill 
					where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的采购退货出库单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		$warehouseId = $data[0]["warehouse_id"];
		$bizDT = $this->toYMD($data[0]["bizdt"]);
		$bizUserId = $data[0]["biz_user_id"];
		$allRejMoney = $data[0]["rejection_money"];
		$supplierId = $data[0]["supplier_id"];
		$receivingType = $data[0]["receiving_type"];
		$companyId = $data[0]["company_id"];
		
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("采购退货出库单(单号：$ref)已经提交，不能再次提交");
		}
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("要出库的仓库不存在");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库[$warehouseName]还没有完成库存建账，不能进行出库操作");
		}
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法完成提交操作");
		}
		$sql = "select name from t_supplier where id = '%s' ";
		$data = $db->query($sql, $supplierId);
		if (! $data) {
			$db->rollback();
			return $this->bad("供应商不存在，无法完成提交操作");
		}
		
		$allReceivingType = array(
				0,
				1
		);
		if (! in_array($receivingType, $allReceivingType)) {
			$db->rollback();
			return $this->bad("收款方式不正确，无法完成提交操作");
		}
		
		$sql = "select goods_id, rejection_goods_count as rej_count,
						rejection_money as rej_money,
						goods_count, goods_price, pwbilldetail_id
					from t_pr_bill_detail
					where prbill_id = '%s'
					order by show_order";
		$items = $db->query($sql, $id);
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goods_id"];
			$rejCount = $v["rej_count"];
			$rejMoney = $v["rej_money"];
			$goodsCount = $v["goods_count"];
			$goodsPricePurchase = $v["goods_price"];
			
			$pwbillDetailId = $v["pwbilldetail_id"];
			
			if ($rejCount == 0) {
				continue;
			}
			
			if ($rejCount < 0) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的退货数量不能为负数");
			}
			if ($rejCount > $goodsCount) {
				$db->rollback();
				$index = $i + 1;
				return $this->bad("第{$index}条记录的退货数量不能大于采购数量");
			}
			
			if ($fifo) {
				// 先进先出
				
				$sql = "select balance_count, balance_price, balance_money,
								out_count, out_money, date_created
							from t_inventory_fifo
							where pwbilldetail_id = '%s' ";
				$data = $db->query($sql, $pwbillDetailId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$fifoDateCreated = $data[0]["date_created"];
				$fifoOutCount = $data[0]["out_count"];
				if (! $fifoOutCount) {
					$fifoOutCount = 0;
				}
				$fifoOutMoney = $data[0]["out_money"];
				if (! $fifoOutMoney) {
					$fifoOutMoney = 0;
				}
				$fifoBalanceCount = $data[0]["balance_count"];
				if ($fifoBalanceCount < $rejCount) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$fifoBalancePrice = $data[0]["balance_price"];
				$fifoBalanceMoney = $data[0]["balance_money"];
				$outMoney = 0;
				if ($rejCount == $fifoBalanceCount) {
					$outMoney = $fifoBalanceMoney;
				} else {
					$outMoney = $fifoBalancePrice * $rejCount;
				}
				
				// 库存总账
				$sql = "select balance_count, balance_price, balance_money,
							out_count, out_money
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$balanceCount = $data[0]["balance_count"];
				$balancePrice = $data[0]["balance_price"];
				$balanceMoney = $data[0]["balance_money"];
				
				$totalOutCount = $data[0]["out_count"];
				$totalOutMoney = $data[0]["out_money"];
				
				$outCount = $rejCount;
				$outPrice = $outMoney / $rejCount;
				
				$totalOutCount += $outCount;
				$totalOutMoney += $outMoney;
				$totalOutPrice = $totalOutMoney / $totalOutCount;
				$balanceCount -= $outCount;
				if ($balanceCount == 0) {
					$balanceMoney -= $outMoney;
					$balancePrice = 0;
				} else {
					$balanceMoney -= $outMoney;
					$balancePrice = $balanceMoney / $balanceCount;
				}
				
				$sql = "update t_inventory
						set out_count = %d, out_price = %f, out_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, 
						$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, biz_date, biz_user_id,
							date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购退货出库')";
				$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
						$ref);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// fifo
				$fvOutCount = $outCount + $fifoOutCount;
				$fvOutMoney = $outMoney + $fifoOutMoney;
				$fvBalanceCount = $fifoBalanceCount - $outCount;
				$fvBalanceMoney = 0;
				if ($fvBalanceCount > 0) {
					$fvBalanceMoney = $fifoBalanceMoney - $outMoney;
				}
				$sql = "update t_inventory_fifo
							set out_count = %d, out_price = %f, out_money = %f, balance_count = %d,
								balance_money = %f
							where pwbilldetail_id = '%s' ";
				$rc = $db->execute($sql, $fvOutCount, $fvOutMoney / $fvOutCount, $fvOutMoney, 
						$fvBalanceCount, $fvBalanceMoney, $pwbillDetailId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// fifo的明细记录
				$sql = "insert into t_inventory_fifo_detail(date_created, 
								out_count, out_price, out_money, balance_count, balance_price, balance_money,
								warehouse_id, goods_id)
							values ('%s', %d, %f, %f, %d, %f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $fifoDateCreated, $outCount, $outPrice, $outMoney, 
						$fvBalanceCount, $outPrice, $fvBalanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				// 移动平均法
				
				// 库存总账
				$sql = "select balance_count, balance_price, balance_money,
							out_count, out_money
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$balanceCount = $data[0]["balance_count"];
				$balancePrice = $data[0]["balance_price"];
				$balanceMoney = $data[0]["balance_money"];
				if ($rejCount > $balanceCount) {
					$db->rollback();
					$index = $i + 1;
					return $this->bad("第{$index}条商品库存不足，无法退货");
				}
				$totalOutCount = $data[0]["out_count"];
				$totalOutMoney = $data[0]["out_money"];
				
				$outCount = $rejCount;
				$outMoney = $goodsPricePurchase * $outCount;
				$outPrice = $goodsPricePurchase;
				
				$totalOutCount += $outCount;
				$totalOutMoney += $outMoney;
				$totalOutPrice = $totalOutMoney / $totalOutCount;
				$balanceCount -= $outCount;
				if ($balanceCount == 0) {
					$balanceMoney -= $outMoney;
					$balancePrice = 0;
				} else {
					$balanceMoney -= $outMoney;
					$balancePrice = $balanceMoney / $balanceCount;
				}
				
				$sql = "update t_inventory
						set out_count = %d, out_price = %f, out_money = %f,
							balance_count = %d, balance_price = %f, balance_money = %f
						where warehouse_id = '%s' and goods_id = '%s' ";
				$rc = $db->execute($sql, $totalOutCount, $totalOutPrice, $totalOutMoney, 
						$balanceCount, $balancePrice, $balanceMoney, $warehouseId, $goodsId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 库存明细账
				$sql = "insert into t_inventory_detail(out_count, out_price, out_money, balance_count,
							balance_price, balance_money, warehouse_id, goods_id, biz_date, biz_user_id,
							date_created, ref_number, ref_type)
						values (%d, %f, %f, %d, %f, %f, '%s', '%s', '%s', '%s', now(), '%s', '采购退货出库')";
				$rc = $db->execute($sql, $outCount, $outPrice, $outMoney, $balanceCount, 
						$balancePrice, $balanceMoney, $warehouseId, $goodsId, $bizDT, $bizUserId, 
						$ref);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError();
				}
			}
		}
		
		$idGen = new IdGenService();
		
		if ($receivingType == 0) {
			// 记应收账款
			// 应收总账
			$sql = "select rv_money, balance_money
					from t_receivables
					where ca_id = '%s' and ca_type = 'supplier'
						and company_id = '%s' ";
			$data = $db->query($sql, $supplierId, $companyId);
			if (! $data) {
				$sql = "insert into t_receivables(id, rv_money, act_money, balance_money, ca_id, ca_type,
							company_id)
						values ('%s', %f, 0, %f, '%s', 'supplier', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $allRejMoney, $allRejMoney, $supplierId, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$rvMoney = $data[0]["rv_money"];
				$balanceMoney = $data[0]["balance_money"];
				$rvMoney += $allRejMoney;
				$balanceMoney += $allRejMoney;
				$sql = "update t_receivables
						set rv_money = %f, balance_money = %f
						where ca_id = '%s' and ca_type = 'supplier'
							and company_id = '%s' ";
				$rc = $db->execute($sql, $rvMoney, $balanceMoney, $supplierId, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 应收明细账
			$sql = "insert into t_receivables_detail(id, rv_money, act_money, balance_money, ca_id, ca_type,
						biz_date, date_created, ref_number, ref_type, company_id)
					values ('%s', %f, 0, %f, '%s', 'supplier', '%s', now(), '%s', '采购退货出库', '%s')";
			$rc = $db->execute($sql, $idGen->newId(), $allRejMoney, $allRejMoney, $supplierId, 
					$bizDT, $ref, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		} else if ($receivingType == 1) {
			// 现金收款
			$inCash = $allRejMoney;
			
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
				
				$balanceCash = $sumInMoney - $sumOutMoney + $inCash;
				$sql = "insert into t_cash(in_money, balance_money, biz_date, company_id)
							values (%f, %f, '%s', '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(in_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购退货出库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$balanceCash = $data[0]["balance_money"] + $inCash;
				$sumInMoney = $data[0]["in_money"] + $inCash;
				$sql = "update t_cash
							set in_money = %f, balance_money = %f
							where biz_date = '%s' and company_id = '%s' ";
				$db->execute($sql, $sumInMoney, $balanceCash, $bizDT, $companyId);
				
				// 记现金明细账
				$sql = "insert into t_cash_detail(in_money, balance_money, biz_date, ref_type,
								ref_number, date_created, company_id)
							values (%f, %f, '%s', '采购退货出库', '%s', now(), '%s')";
				$rc = $db->execute($sql, $inCash, $balanceCash, $bizDT, $ref, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 调整业务日期之后的现金总账和明细账的余额
			$sql = "update t_cash
							set balance_money = balance_money + %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $inCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "update t_cash_detail
							set balance_money = balance_money + %f
							where biz_date > '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $inCash, $bizDT, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 修改单据本身的状态
		$sql = "update t_pr_bill
					set bill_status = 1000
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$log = "提交采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}