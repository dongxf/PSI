<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\SRBillDAO;

/**
 * 销售退货入库单Service
 *
 * @author 李静波
 */
class SRBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "销售退货入库";

	/**
	 * 销售退货入库单主表信息列表
	 */
	public function srbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new SRBillDAO($this->db());
		return $dao->srbillList($params);
	}

	/**
	 * 销售退货入库单明细信息列表
	 */
	public function srBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SRBillDAO($this->db());
		return $dao->srBillDetailList($params);
	}

	/**
	 * 获得退货入库单单据数据
	 */
	public function srBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new SRBillDAO($this->db());
		return $dao->srBillInfo($params);
	}

	/**
	 * 列出要选择的可以做退货入库的销售出库单
	 */
	public function selectWSBillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new SRBillDAO($this->db());
		return $dao->selectWSBillList($params);
	}

	/**
	 * 新增或编辑销售退货入库单
	 */
	public function editSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new SRBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑
			
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updateSRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "编辑销售退货入库单，单号：{$ref}";
		} else {
			// 新增
			
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->addSRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建销售退货入库单，单号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得销售出库单的信息
	 */
	public function getWSBillInfoForSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SRBillDAO($this->db());
		return $dao->getWSBillInfoForSRBill($params);
	}

	/**
	 * 删除销售退货入库单
	 */
	public function deleteSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new SRBillDAO($db);
		$rc = $dao->deleteSRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$bs = new BizlogService($db);
		$log = "删除销售退货入库单，单号：{$ref}";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交销售退货入库单
	 */
	public function commitSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$us = new UserService();
		
		$bs = new BizConfigService();
		$fifo = $bs->getInventoryMethod() == 1; // true: 先进先出
		
		$sql = "select ref, bill_status, warehouse_id, customer_id, bizdt, 
					biz_user_id, rejection_sale_money, payment_type,
					company_id
				from t_sr_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要提交的销售退货入库单不存在");
		}
		$billStatus = $data[0]["bill_status"];
		$ref = $data[0]["ref"];
		if ($billStatus != 0) {
			$db->rollback();
			return $this->bad("销售退货入库单(单号:{$ref})已经提交，不能再次提交");
		}
		
		$paymentType = $data[0]["payment_type"];
		$warehouseId = $data[0]["warehouse_id"];
		$customerId = $data[0]["customer_id"];
		$bizDT = $data[0]["bizdt"];
		$bizUserId = $data[0]["biz_user_id"];
		$rejectionSaleMoney = $data[0]["rejection_sale_money"];
		$companyId = $data[0]["company_id"];
		
		$sql = "select name, inited from t_warehouse where id = '%s' ";
		$data = $db->query($sql, $warehouseId);
		if (! $data) {
			$db->rollback();
			return $this->bad("仓库不存在，无法提交");
		}
		$warehouseName = $data[0]["name"];
		$inited = $data[0]["inited"];
		if ($inited != 1) {
			$db->rollback();
			return $this->bad("仓库[{$warehouseName}]还没有建账");
		}
		
		$sql = "select name from t_customer where id = '%s' ";
		$data = $db->query($sql, $customerId);
		if (! $data) {
			$db->rollback();
			return $this->bad("客户不存在，无法提交");
		}
		
		$sql = "select name from t_user where id = '%s' ";
		$data = $db->query($sql, $bizUserId);
		if (! $data) {
			$db->rollback();
			return $this->bad("业务人员不存在，无法提交");
		}
		
		$allPaymentType = array(
				0,
				1,
				2
		);
		if (! in_array($paymentType, $allPaymentType)) {
			$db->rollback();
			return $this->bad("付款方式不正确，无法提交");
		}
		
		// 检查退货数量
		// 1、不能为负数
		// 2、累计退货数量不能超过销售的数量
		$sql = "select wsbilldetail_id, rejection_goods_count, goods_id
				from t_sr_bill_detail
				where srbill_id = '%s' 
				order by show_order";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("销售退货入库单(单号:{$ref})没有退货明细，无法提交");
		}
		
		foreach ( $data as $i => $v ) {
			$wsbillDetailId = $v["wsbilldetail_id"];
			$rejCount = $v["rejection_goods_count"];
			$goodsId = $v["goods_id"];
			
			// 退货数量为负数
			if ($rejCount < 0) {
				$sql = "select code, name, spec
						from t_goods
						where id = '%s' ";
				$data = $db->query($sql, $goodsId);
				if ($data) {
					$db->rollback();
					$goodsInfo = "编码：" . $data[0]["code"] . " 名称：" . $data[0]["name"] . " 规格：" . $data[0]["spec"];
					return $this->bad("商品({$goodsInfo})退货数量不能为负数");
				} else {
					$db->rollback();
					return $this->bad("商品退货数量不能为负数");
				}
			}
			
			// 累计退货数量不能超过销售数量
			$sql = "select goods_count from t_ws_bill_detail where id = '%s' ";
			$data = $db->query($sql, $wsbillDetailId);
			$saleGoodsCount = 0;
			if ($data) {
				$saleGoodsCount = $data[0]["goods_count"];
			}
			
			$sql = "select sum(d.rejection_goods_count) as rej_count
					from t_sr_bill s, t_sr_bill_detail d
					where s.id = d.srbill_id and s.bill_status <> 0 
					  and d.wsbilldetail_id = '%s' ";
			$data = $db->query($sql, $wsbillDetailId);
			$totalRejCount = $data[0]["rej_count"] + $rejCount;
			if ($totalRejCount > $saleGoodsCount) {
				$sql = "select code, name, spec
						from t_goods
						where id = '%s' ";
				$data = $db->query($sql, $goodsId);
				if ($data) {
					$db->rollback();
					$goodsInfo = "编码：" . $data[0]["code"] . " 名称：" . $data[0]["name"] . " 规格：" . $data[0]["spec"];
					return $this->bad("商品({$goodsInfo})累计退货数量不超过销售量");
				} else {
					$db->rollback();
					return $this->bad("商品累计退货数量不超过销售量");
				}
			}
		}
		
		$sql = "select goods_id, rejection_goods_count, inventory_money
				from t_sr_bill_detail
				where srbill_id = '%s' 
				order by show_order";
		$items = $db->query($sql, $id);
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goods_id"];
			$rejCount = $v["rejection_goods_count"];
			$rejMoney = $v["inventory_money"];
			if ($rejCount == 0) {
				continue;
			}
			$rejPrice = $rejMoney / $rejCount;
			
			if ($fifo) {
				// TODO 先进先出
			} else {
				// 移动平均
				$sql = "select in_count, in_money, balance_count, balance_money
						from t_inventory
						where warehouse_id = '%s' and goods_id = '%s' ";
				$data = $db->query($sql, $warehouseId, $goodsId);
				if (! $data) {
					$totalInCount = 0;
					$totalInMoney = 0;
					$totalBalanceCount = 0;
					$totalBalanceMoney = 0;
					
					$totalInCount += $rejCount;
					$totalInMoney += $rejMoney;
					$totalInPrice = $totalInMoney / $totalInCount;
					$totalBalanceCount += $rejCount;
					$totalBalanceMoney += $rejMoney;
					$totalBalancePrice = $totalBalanceMoney / $totalBalanceCount;
					
					// 库存明细账
					$sql = "insert into t_inventory_detail(in_count, in_price, in_money,
						balance_count, balance_price, balance_money, ref_number, ref_type,
						biz_date, biz_user_id, date_created, goods_id, warehouse_id)
						values (%d, %f, %f, 
						%d, %f, %f, '%s', '销售退货入库',
						'%s', '%s', now(), '%s', '%s')";
					$rc = $db->execute($sql, $rejCount, $rejPrice, $rejMoney, $totalBalanceCount, 
							$totalBalancePrice, $totalBalanceMoney, $ref, $bizDT, $bizUserId, 
							$goodsId, $warehouseId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 库存总账
					$sql = "insert into t_inventory(in_count, in_price, in_money,
						balance_count, balance_price, balance_money, 
						goods_id, warehouse_id)
						values (%d, %f, %f, 
						%d, %f, %f, '%s', '%s')";
					$rc = $db->execute($sql, $totalInCount, $totalInPrice, $totalInMoney, 
							$totalBalanceCount, $totalBalancePrice, $totalBalanceMoney, $goodsId, 
							$warehouseId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				} else {
					$totalInCount = $data[0]["in_count"];
					$totalInMoney = $data[0]["in_money"];
					$totalBalanceCount = $data[0]["balance_count"];
					$totalBalanceMoney = $data[0]["balance_money"];
					
					$totalInCount += $rejCount;
					$totalInMoney += $rejMoney;
					$totalInPrice = $totalInMoney / $totalInCount;
					$totalBalanceCount += $rejCount;
					$totalBalanceMoney += $rejMoney;
					$totalBalancePrice = $totalBalanceMoney / $totalBalanceCount;
					
					// 库存明细账
					$sql = "insert into t_inventory_detail(in_count, in_price, in_money,
						balance_count, balance_price, balance_money, ref_number, ref_type,
						biz_date, biz_user_id, date_created, goods_id, warehouse_id)
						values (%d, %f, %f, 
						%d, %f, %f, '%s', '销售退货入库',
						'%s', '%s', now(), '%s', '%s')";
					$rc = $db->execute($sql, $rejCount, $rejPrice, $rejMoney, $totalBalanceCount, 
							$totalBalancePrice, $totalBalanceMoney, $ref, $bizDT, $bizUserId, 
							$goodsId, $warehouseId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 库存总账
					$sql = "update t_inventory
						set in_count = %d, in_price = %f, in_money = %f,
						  balance_count = %d, balance_price = %f, balance_money = %f
						where goods_id = '%s' and warehouse_id = '%s' ";
					$rc = $db->execute($sql, $totalInCount, $totalInPrice, $totalInMoney, 
							$totalBalanceCount, $totalBalancePrice, $totalBalanceMoney, $goodsId, 
							$warehouseId);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
		}
		
		$idGen = new IdGenService();
		
		if ($paymentType == 0) {
			// 记应付账款
			// 应付账款总账
			$sql = "select pay_money, balance_money
					from t_payables
					where ca_id = '%s' and ca_type = 'customer' 
						and company_id = '%s' ";
			$data = $db->query($sql, $customerId, $companyId);
			if ($data) {
				$totalPayMoney = $data[0]["pay_money"];
				$totalBalanceMoney = $data[0]["balance_money"];
				
				$totalPayMoney += $rejectionSaleMoney;
				$totalBalanceMoney += $rejectionSaleMoney;
				$sql = "update t_payables
						set pay_money = %f, balance_money = %f
						where ca_id = '%s' and ca_type = 'customer' 
							and company_id = '%s' ";
				$rc = $db->execute($sql, $totalPayMoney, $totalBalanceMoney, $customerId, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				
				$sql = "insert into t_payables (id, ca_id, ca_type, pay_money, balance_money, 
							act_money, company_id)
						values ('%s', '%s', 'customer', %f, %f, %f, '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $customerId, $rejectionSaleMoney, 
						$rejectionSaleMoney, 0, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 应付账款明细账
			$sql = "insert into t_payables_detail(id, ca_id, ca_type, pay_money, balance_money,
					biz_date, date_created, ref_number, ref_type, act_money, company_id)
					values ('%s', '%s', 'customer', %f, %f,
					 '%s', now(), '%s', '销售退货入库', 0, '%s')";
			$rc = $db->execute($sql, $idGen->newId(), $customerId, $rejectionSaleMoney, 
					$rejectionSaleMoney, $bizDT, $ref, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		} else if ($paymentType == 1) {
			// 现金付款
			$outCash = $rejectionSaleMoney;
			
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
							values (%f, %f, '%s', '销售退货入库', '%s', now(), '%s')";
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
							values (%f, %f, '%s', '销售退货入库', '%s', now(), '%s')";
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
			// 2： 退款转入预收款
			$inMoney = $rejectionSaleMoney;
			
			// 预收款总账
			$totalInMoney = 0;
			$totalBalanceMoney = 0;
			$sql = "select in_money, balance_money 
						from t_pre_receiving
						where customer_id = '%s' and company_id = '%s' ";
			$data = $db->query($sql, $customerId, $companyId);
			if (! $data) {
				$totalInMoney = $inMoney;
				$totalBalanceMoney = $inMoney;
				$sql = "insert into t_pre_receiving(id, customer_id, in_money, balance_money, company_id)
							values ('%s', '%s', %f, %f, '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $customerId, $totalInMoney, 
						$totalBalanceMoney, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			} else {
				$totalInMoney = $data[0]["in_money"];
				if (! $totalInMoney) {
					$totalInMoney = 0;
				}
				$totalBalanceMoney = $data[0]["balance_money"];
				if (! $totalBalanceMoney) {
					$totalBalanceMoney = 0;
				}
				$totalInMoney += $inMoney;
				$totalBalanceMoney += $inMoney;
				$sql = "update t_pre_receiving
							set in_money = %f, balance_money = %f
							where customer_id = '%s' and company_id = '%s' ";
				$rc = $db->execute($sql, $totalInMoney, $totalBalanceMoney, $customerId, $companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 预收款明细账
			$sql = "insert into t_pre_receiving_detail(id, customer_id, in_money, balance_money,
						biz_date, date_created, ref_number, ref_type, biz_user_id, input_user_id,
						company_id)
					values('%s', '%s', %f, %f, '%s', now(), '%s', '销售退货入库', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $idGen->newId(), $customerId, $inMoney, $totalBalanceMoney, 
					$bizDT, $ref, $bizUserId, $us->getLoginUserId(), $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		// 把单据本身的状态修改为已经提交
		$sql = "update t_sr_bill
					set bill_status = 1000
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "提交销售退货入库单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}