<?php

namespace Home\Service;

/**
 * 采购订单Service
 *
 * @author 李静波
 */
class POBillService extends PSIBaseService {

	/**
	 * 生成新的采购订单号
	 */
	private function genNewBillRef() {
		$pre = "PO";
		$mid = date("Ymd");
		
		$sql = "select ref from t_po_bill where ref like '%s' order by ref desc limit 1";
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

	/**
	 * 获得采购订单主表信息列表
	 */
	public function pobillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$supplierId = $params["supplierId"];
		$paymentType = $params["paymentType"];
		
		$db = M();
		
		$queryParams = array();
		
		$result = array();
		$sql = "select p.id, p.ref, p.bill_status, p.goods_money, p.tax, p.money_with_tax,
					s.name as supplier_name, p.contact, p.tel, p.fax, p.deal_address,
					p.deal_date, p.payment_type, p.bill_memo, p.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					p.confirm_user_id, p.confirm_date
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
				order by p.ref desc";
		
		$sql .= " limit %d , %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["billStatus"] = $v["bill_status"];
			$result[$i]["dealDate"] = $this->toYMD($v["deal_date"]);
			$result[$i]["dealAddress"] = $v["deal_address"];
			$result[$i]["supplierName"] = $v["supplier_name"];
			$result[$i]["contact"] = $v["contact"];
			$result[$i]["tel"] = $v["tel"];
			$result[$i]["fax"] = $v["fax"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["tax"] = $v["tax"];
			$result[$i]["moneyWithTax"] = $v["money_with_tax"];
			$result[$i]["paymentType"] = $v["payment_type"];
			$result[$i]["billMemo"] = $v["bill_memo"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["orgName"] = $v["org_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
			
			$confirmUserId = $v["confirm_user_id"];
			if (! $confirmUserId) {
				$sql = "select name from t_user where id = '%s' ";
				$d = $db->query($sql, $confirmUserId);
				if ($d) {
					$result[$i]["confirmUserName"] = $d[0]["name"];
					$result[$i]["confirmDate"] = $v["confirm_date"];
				}
			}
		}
		
		$sql = "select count(*) as cnt
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
				";
		$queryParams = array();
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
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
		
		$id = $bill["id"];
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$supplierId = $bill["supplierId"];
		$ss = new SupplierService();
		if (! $ss->supplierExists($supplierId, $db)) {
			return $this->bad("供应商不存在");
		}
		$orgId = $bill["orgId"];
		$us = new UserService();
		if (! $us->orgExists($orgId, $db)) {
			return $this->bad("组织机构不存在");
		}
		$bizUserId = $bill["bizUserId"];
		if (! $us->userExists($bizUserId, $db)) {
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
		
		if ($id) {
			// 编辑
			return $this->todo();
		} else {
			// 新建采购订单
			
			$db->startTrans();
			try {
				$id = $idGen->newId();
				$ref = $this->genNewBillRef();
				// 主表
				$sql = "insert into t_po_bill(id, ref, bill_status, deal_date, biz_dt, org_id, biz_user_id,
							goods_money, tax, money_with_tax, input_user_id, supplier_id, contact, tel, fax,
							deal_address, bill_memo, payment_type, date_created)
						values ('%s', '%s', 0, '%s', '%s', '%s', '%s', 
							0, 0, 0, '%s', '%s', '%s', '%s', '%s', 
							'%s', '%s', %d, now())";
				$rc = $db->execute($sql, $id, $ref, $dealDate, $dealDate, $orgId, $bizUserId, 
						$us->getLoginUserId(), $supplierId, $contact, $tel, $fax, $dealAddress, 
						$billMemo, $paymentType);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
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
								goods_price, pobill_id, tax_rate, tax, money_with_tax, pw_count, left_count, show_order)
							values ('%s', now(), '%s', %d, %f,
								%f, '%s', %d, %f, %f, 0, %d, %d)";
					$rc = $db->execute($sql, $idGen->newId(), $goodsId, $goodsCount, $goodsMoney, 
							$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i);
					if (! $rc) {
						$db->rollback();
						return $this->sqlError();
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
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				$db->commit();
			} catch ( Exception $e ) {
				$db->rollback();
				return $this->sqlError();
			}
		}
		
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
		}
		
		return $result;
	}
	
	public function poBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}

		$id = $params["id"];
		$db = M();
		
		$sql = "select p.id, g.code, g.name, g.spec, p.goods_count, p.goods_price, p.goods_money,
					p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["goodsCode"] = $v["code"];
			$result[$i]["goodsName"] = $v["name"];
			$result[$i]["goodsSpec"] = $v["spec"];
			$result[$i]["goodsCount"] = $v["goods_count"];
			$result[$i]["goodsPrice"] = $v["goods_price"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["taxRate"] = $v["tax_rate"];
			$result[$i]["tax"] = $v["tax"];
			$result[$i]["moneyWithTax"] = $v["money_with_tax"];
			$result[$i]["unitName"] = $v["unit_name"];
		}
		
		return $result;
	}
}