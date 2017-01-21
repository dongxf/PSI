<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\POBillDAO;

/**
 * 采购订单Service
 *
 * @author 李静波
 */
class POBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购订单";

	/**
	 * 生成新的采购订单号
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getPOBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_po_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
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
	 * 获得采购订单主表信息列表
	 */
	public function pobillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new POBillDAO();
		return $dao->pobillList($params);
	}

	/**
	 * 新建或编辑采购订单
	 */
	public function editPOBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		
		$db->startTrans();
		
		$id = $bill["id"];
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			$db->rollback();
			return $this->bad("交货日期不正确");
		}
		
		$supplierId = $bill["supplierId"];
		$ss = new SupplierService();
		if (! $ss->supplierExists($supplierId, $db)) {
			$db->rollback();
			return $this->bad("供应商不存在");
		}
		$orgId = $bill["orgId"];
		$us = new UserService();
		if (! $us->orgExists($orgId, $db)) {
			$db->rollback();
			return $this->bad("组织机构不存在");
		}
		$bizUserId = $bill["bizUserId"];
		if (! $us->userExists($bizUserId, $db)) {
			$db->rollback();
			return $this->bad("业务员不存在");
		}
		$paymentType = $bill["paymentType"];
		$contact = $bill["contact"];
		$tel = $bill["tel"];
		$fax = $bill["fax"];
		$dealAddress = $bill["dealAddress"];
		$billMemo = $bill["billMemo"];
		
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		
		$companyId = $us->getCompanyId();
		if (! $companyId) {
			$db->rollback();
			return $this->bad("所属公司不存在");
		}
		
		$log = null;
		if ($id) {
			// 编辑
			$sql = "select ref, data_org, bill_status, company_id from t_po_bill where id = '%s' ";
			$data = $db->query($sql, $id);
			if (! $data) {
				$db->rollback();
				return $this->bad("要编辑的采购订单不存在");
			}
			$ref = $data[0]["ref"];
			$dataOrg = $data[0]["data_org"];
			$companyId = $data[0]["company_id"];
			$billStatus = $data[0]["bill_status"];
			if ($billStatus != 0) {
				$db->rollback();
				return $this->bad("当前采购订单已经审核，不能再编辑");
			}
			
			$sql = "delete from t_po_bill_detail where pobill_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				$goodsCount = $v["goodsCount"];
				$goodsPrice = $v["goodsPrice"];
				$goodsMoney = $v["goodsMoney"];
				$taxRate = $v["taxRate"];
				$tax = $v["tax"];
				$moneyWithTax = $v["moneyWithTax"];
				
				$sql = "insert into t_po_bill_detail(id, date_created, goods_id, goods_count, goods_money,
							goods_price, pobill_id, tax_rate, tax, money_with_tax, pw_count, left_count, 
							show_order, data_org, company_id)
						values ('%s', now(), '%s', %d, %f,
							%f, '%s', %d, %f, %f, 0, %d, %d, '%s', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, 
						$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 同步主表的金额合计字段
			$sql = "select sum(goods_money) as sum_goods_money, sum(tax) as sum_tax, 
							sum(money_with_tax) as sum_money_with_tax
						from t_po_bill_detail
						where pobill_id = '%s' ";
			$data = $db->query($sql, $id);
			$sumGoodsMoney = $data[0]["sum_goods_money"];
			if (! $sumGoodsMoney) {
				$sumGoodsMoney = 0;
			}
			$sumTax = $data[0]["sum_tax"];
			if (! $sumTax) {
				$sumTax = 0;
			}
			$sumMoneyWithTax = $data[0]["sum_money_with_tax"];
			if (! $sumMoneyWithTax) {
				$sumMoneyWithTax = 0;
			}
			
			$sql = "update t_po_bill
					set goods_money = %f, tax = %f, money_with_tax = %f,
						deal_date = '%s', supplier_id = '%s',
						deal_address = '%s', contact = '%s', tel = '%s', fax = '%s',
						org_id = '%s', biz_user_id = '%s', payment_type = %d,
						bill_memo = '%s', input_user_id = '%s', date_created = now()
					where id = '%s' ";
			$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $dealDate, 
					$supplierId, $dealAddress, $contact, $tel, $fax, $orgId, $bizUserId, 
					$paymentType, $billMemo, $us->getLoginUserId(), $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑采购订单，单号：{$ref}";
		} else {
			// 新建采购订单
			
			$id = $idGen->newId();
			$ref = $this->genNewBillRef();
			
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			// 主表
			$sql = "insert into t_po_bill(id, ref, bill_status, deal_date, biz_dt, org_id, biz_user_id,
							goods_money, tax, money_with_tax, input_user_id, supplier_id, contact, tel, fax,
							deal_address, bill_memo, payment_type, date_created, data_org, company_id)
						values ('%s', '%s', 0, '%s', '%s', '%s', '%s', 
							0, 0, 0, '%s', '%s', '%s', '%s', '%s', 
							'%s', '%s', %d, now(), '%s', '%s')";
			$rc = $db->execute($sql, $id, $ref, $dealDate, $dealDate, $orgId, $bizUserId, 
					$us->getLoginUserId(), $supplierId, $contact, $tel, $fax, $dealAddress, 
					$billMemo, $paymentType, $dataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			// 明细记录
			foreach ( $items as $i => $v ) {
				$goodsId = $v["goodsId"];
				if (! $goodsId) {
					continue;
				}
				$goodsCount = $v["goodsCount"];
				$goodsPrice = $v["goodsPrice"];
				$goodsMoney = $v["goodsMoney"];
				$taxRate = $v["taxRate"];
				$tax = $v["tax"];
				$moneyWithTax = $v["moneyWithTax"];
				
				$sql = "insert into t_po_bill_detail(id, date_created, goods_id, goods_count, goods_money,
								goods_price, pobill_id, tax_rate, tax, money_with_tax, pw_count, left_count, 
								show_order, data_org, company_id)
							values ('%s', now(), '%s', %d, %f,
								%f, '%s', %d, %f, %f, 0, %d, %d, '%s', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, 
						$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
						$companyId);
				if ($rc === false) {
					$db->rollback();
					return $this->sqlError(__LINE__);
				}
			}
			
			// 同步主表的金额合计字段
			$sql = "select sum(goods_money) as sum_goods_money, sum(tax) as sum_tax, 
							sum(money_with_tax) as sum_money_with_tax
						from t_po_bill_detail
						where pobill_id = '%s' ";
			$data = $db->query($sql, $id);
			$sumGoodsMoney = $data[0]["sum_goods_money"];
			if (! $sumGoodsMoney) {
				$sumGoodsMoney = 0;
			}
			$sumTax = $data[0]["sum_tax"];
			if (! $sumTax) {
				$sumTax = 0;
			}
			$sumMoneyWithTax = $data[0]["sum_money_with_tax"];
			if (! $sumMoneyWithTax) {
				$sumMoneyWithTax = 0;
			}
			
			$sql = "update t_po_bill
						set goods_money = %f, tax = %f, money_with_tax = %f
						where id = '%s' ";
			$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新建采购订单，单号：{$ref}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得采购订单的信息
	 */
	public function poBillInfo($params) {
		$id = $params["id"];
		
		$result = array();
		
		$cs = new BizConfigService();
		$result["taxRate"] = $cs->getTaxRate();
		
		$db = M();
		
		if ($id) {
			// 编辑采购订单
			$sql = "select p.ref, p.deal_date, p.deal_address, p.supplier_id,
						s.name as supplier_name, p.contact, p.tel, p.fax,
						p.org_id, o.full_name, p.biz_user_id, u.name as biz_user_name,
						p.payment_type, p.bill_memo, p.bill_status
					from t_po_bill p, t_supplier s, t_user u, t_org o
					where p.id = '%s' and p.supplier_Id = s.id
						and p.biz_user_id = u.id
						and p.org_id = o.id";
			$data = $db->query($sql, $id);
			if ($data) {
				$v = $data[0];
				$result["ref"] = $v["ref"];
				$result["dealDate"] = $this->toYMD($v["deal_date"]);
				$result["dealAddress"] = $v["deal_address"];
				$result["supplierId"] = $v["supplier_id"];
				$result["supplierName"] = $v["supplier_name"];
				$result["contact"] = $v["contact"];
				$result["tel"] = $v["tel"];
				$result["fax"] = $v["fax"];
				$result["orgId"] = $v["org_id"];
				$result["orgFullName"] = $v["full_name"];
				$result["bizUserId"] = $v["biz_user_id"];
				$result["bizUserName"] = $v["biz_user_name"];
				$result["paymentType"] = $v["payment_type"];
				$result["billMemo"] = $v["bill_memo"];
				$result["billStatus"] = $v["bill_status"];
				
				// 明细表
				$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, p.goods_count, p.goods_price, p.goods_money,
					p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
				$items = array();
				$data = $db->query($sql, $id);
				
				foreach ( $data as $i => $v ) {
					$items[$i]["goodsId"] = $v["goods_id"];
					$items[$i]["goodsCode"] = $v["code"];
					$items[$i]["goodsName"] = $v["name"];
					$items[$i]["goodsSpec"] = $v["spec"];
					$items[$i]["goodsCount"] = $v["goods_count"];
					$items[$i]["goodsPrice"] = $v["goods_price"];
					$items[$i]["goodsMoney"] = $v["goods_money"];
					$items[$i]["taxRate"] = $v["tax_rate"];
					$items[$i]["tax"] = $v["tax"];
					$items[$i]["moneyWithTax"] = $v["money_with_tax"];
					$items[$i]["unitName"] = $v["unit_name"];
				}
				
				$result["items"] = $items;
			}
		} else {
			// 新建采购订单
			$us = new UserService();
			$result["bizUserId"] = $us->getLoginUserId();
			$result["bizUserName"] = $us->getLoginUserName();
			
			$sql = "select o.id, o.full_name
					from t_org o, t_user u
					where o.id = u.org_id and u.id = '%s' ";
			$data = $db->query($sql, $us->getLoginUserId());
			if ($data) {
				$result["orgId"] = $data[0]["id"];
				$result["orgFullName"] = $data[0]["full_name"];
			}
			
			// 采购订单默认付款方式
			$bc = new BizConfigService();
			$result["paymentType"] = $bc->getPOBillDefaultPayment();
		}
		
		return $result;
	}

	/**
	 * 采购订单的商品明细
	 */
	public function poBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new POBillDAO();
		return $dao->poBillDetailList($params);
	}

	/**
	 * 审核采购订单
	 */
	public function commitPOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要审核的采购订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$sql = "update t_po_bill
					set bill_status = 1000,
						confirm_user_id = '%s',
						confirm_date = now()
					where id = '%s' ";
		$us = new UserService();
		$rc = $db->execute($sql, $us->getLoginUserId(), $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "审核采购订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除采购订单
	 */
	public function deletePOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的采购订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号：{$ref})已经审核，不能被删除");
		}
		
		$sql = "delete from t_po_bill_detail where pobill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$sql = "delete from t_po_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除采购订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 取消审核采购订单
	 */
	public function cancelConfirmPOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$sql = "select ref, bill_status from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要取消审核的采购订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 1000) {
			$db->rollback();
			return $this->bad("采购订单(单号:{$ref})不能取消审核");
		}
		
		$sql = "select count(*) as cnt from t_po_pw where po_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号:{$ref})已经生成了采购入库单，不能取消审核");
		}
		
		$sql = "update t_po_bill
					set bill_status = 0, confirm_user_id = null, confirm_date = null
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "取消审核采购订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}