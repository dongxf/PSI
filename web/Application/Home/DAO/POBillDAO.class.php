<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 采购订单 DAO
 *
 * @author 李静波
 */
class POBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的采购订单号
	 *
	 * @param string $companyId        	
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getPOBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_po_bill where ref like '%s' order by ref desc limit 1";
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
	 * 获得采购订单主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function pobillList($params) {
		$db = $this->db;
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$supplierId = $params["supplierId"];
		$paymentType = $params["paymentType"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$queryParams = [];
		
		$result = [];
		$sql = "select p.id, p.ref, p.bill_status, p.goods_money, p.tax, p.money_with_tax,
					s.name as supplier_name, p.contact, p.tel, p.fax, p.deal_address,
					p.deal_date, p.payment_type, p.bill_memo, p.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					p.confirm_user_id, p.confirm_date
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) ";
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_ORDER, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			if ($billStatus < 4000) {
				$sql .= " and (p.bill_status = %d) ";
			} else {
				// 订单关闭 - 有多种状态
				$sql .= " and (p.bill_status >= %d) ";
			}
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (p.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s')";
			$queryParams[] = $supplierId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		$sql .= " order by p.deal_date desc, p.ref desc
				  limit %d , %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $v ) {
			$confirmUserName = null;
			$confirmDate = null;
			$confirmUserId = $v["confirm_user_id"];
			if ($confirmUserId) {
				$sql = "select name from t_user where id = '%s' ";
				$d = $db->query($sql, $confirmUserId);
				if ($d) {
					$confirmUserName = $d[0]["name"];
					$confirmDate = $v["confirm_date"];
				}
			}
			
			$result[] = [
					"id" => $v["id"],
					"ref" => $v["ref"],
					"billStatus" => $v["bill_status"],
					"dealDate" => $this->toYMD($v["deal_date"]),
					"dealAddress" => $v["deal_address"],
					"supplierName" => $v["supplier_name"],
					"contact" => $v["contact"],
					"tel" => $v["tel"],
					"fax" => $v["fax"],
					"goodsMoney" => $v["goods_money"],
					"tax" => $v["tax"],
					"moneyWithTax" => $v["money_with_tax"],
					"paymentType" => $v["payment_type"],
					"billMemo" => $v["bill_memo"],
					"bizUserName" => $v["biz_user_name"],
					"orgName" => $v["org_name"],
					"inputUserName" => $v["input_user_name"],
					"dateCreated" => $v["date_created"],
					"confirmUserName" => $confirmUserName,
					"confirmDate" => $confirmDate
			];
		}
		
		$sql = "select count(*) as cnt
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
				";
		$queryParams = [];
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PURCHASE_ORDER, "p", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		if ($billStatus != - 1) {
			if ($billStatus < 4000) {
				$sql .= " and (p.bill_status = %d) ";
			} else {
				// 订单关闭 - 有多种状态
				$sql .= " and (p.bill_status >= %d) ";
			}
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (p.ref like '%s') ";
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (p.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (p.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($supplierId) {
			$sql .= " and (p.supplier_id = '%s')";
			$queryParams[] = $supplierId;
		}
		if ($paymentType != - 1) {
			$sql .= " and (p.payment_type = %d) ";
			$queryParams[] = $paymentType;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return [
				"dataList" => $result,
				"totalCount" => $cnt
		];
	}

	/**
	 * 采购订单的商品明细
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function poBillDetailList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		// id: 采购订单id
		$id = $params["id"];
		
		$sql = "select p.id, g.code, g.name, g.spec, convert(p.goods_count, " . $fmt . ") as goods_count, 
					p.goods_price, p.goods_money,
					convert(p.pw_count, " . $fmt . ") as pw_count, 
					convert(p.left_count, " . $fmt . ") as left_count, p.memo,
					p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
		$result = [];
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"goodsCount" => $v["goods_count"],
					"goodsPrice" => $v["goods_price"],
					"goodsMoney" => $v["goods_money"],
					"taxRate" => $v["tax_rate"],
					"tax" => $v["tax"],
					"moneyWithTax" => $v["money_with_tax"],
					"unitName" => $v["unit_name"],
					"pwCount" => $v["pw_count"],
					"leftCount" => $v["left_count"],
					"memo" => $v["memo"]
			];
		}
		
		return $result;
	}

	/**
	 * 新建采购订单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function addPOBill(& $bill) {
		$db = $this->db;
		
		$dealDate = $bill["dealDate"];
		$supplierId = $bill["supplierId"];
		$orgId = $bill["orgId"];
		$bizUserId = $bill["bizUserId"];
		$paymentType = $bill["paymentType"];
		$contact = $bill["contact"];
		$tel = $bill["tel"];
		$fax = $bill["fax"];
		$dealAddress = $bill["dealAddress"];
		$billMemo = $bill["billMemo"];
		
		$items = $bill["items"];
		
		$dataOrg = $bill["dataOrg"];
		$loginUserId = $bill["loginUserId"];
		$companyId = $bill["companyId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在");
		}
		
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			return $this->bad("组织机构不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		
		// 主表
		$sql = "insert into t_po_bill(id, ref, bill_status, deal_date, biz_dt, org_id, biz_user_id,
					goods_money, tax, money_with_tax, input_user_id, supplier_id, contact, tel, fax,
					deal_address, bill_memo, payment_type, date_created, data_org, company_id)
				values ('%s', '%s', 0, '%s', '%s', '%s', '%s',
					0, 0, 0, '%s', '%s', '%s', '%s', '%s',
					'%s', '%s', %d, now(), '%s', '%s')";
		$rc = $db->execute($sql, $id, $ref, $dealDate, $dealDate, $orgId, $bizUserId, $loginUserId, 
				$supplierId, $contact, $tel, $fax, $dealAddress, $billMemo, $paymentType, $dataOrg, 
				$companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细记录
		$goodsDAO = new GoodsDAO($db);
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			$goods = $goodsDAO->getGoodsById($goodsId);
			if (! $goods) {
				continue;
			}
			
			$goodsCount = $v["goodsCount"];
			if ($goodsCount <= 0) {
				return $this->bad("采购数量需要大于0");
			}
			
			$goodsPrice = $v["goodsPrice"];
			if ($goodsPrice < 0) {
				return $this->bad("采购单价不能是负数");
			}
			
			$goodsMoney = $v["goodsMoney"];
			$taxRate = $v["taxRate"];
			$tax = $v["tax"];
			$moneyWithTax = $v["moneyWithTax"];
			$memo = $v["memo"];
			
			$sql = "insert into t_po_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, pobill_id, tax_rate, tax, money_with_tax, pw_count, left_count,
						show_order, data_org, company_id, memo)
					values ('%s', now(), '%s', convert(%f, $fmt), %f,
						%f, '%s', %d, %f, %f, 0, convert(%f, $fmt), %d, '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
					$companyId, $memo);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑采购订单
	 *
	 * @param array $bill        	
	 * @return NULL|array
	 */
	public function updatePOBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		$poBill = $this->getPOBillById($id);
		if (! $poBill) {
			return $this->bad("要编辑的采购订单不存在");
		}
		
		$ref = $poBill["ref"];
		$dataOrg = $poBill["dataOrg"];
		$companyId = $poBill["companyId"];
		$billStatus = $poBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("当前采购订单已经审核，不能再编辑");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$dealDate = $bill["dealDate"];
		$supplierId = $bill["supplierId"];
		$orgId = $bill["orgId"];
		$bizUserId = $bill["bizUserId"];
		$paymentType = $bill["paymentType"];
		$contact = $bill["contact"];
		$tel = $bill["tel"];
		$fax = $bill["fax"];
		$dealAddress = $bill["dealAddress"];
		$billMemo = $bill["billMemo"];
		
		$items = $bill["items"];
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在");
		}
		
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			return $this->bad("组织机构不存在");
		}
		
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$sql = "delete from t_po_bill_detail where pobill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$goodsDAO = new GoodsDAO($db);
		
		foreach ( $items as $i => $v ) {
			$goodsId = $v["goodsId"];
			if (! $goodsId) {
				continue;
			}
			if (! $goodsDAO->getGoodsById($goodsId)) {
				continue;
			}
			
			$goodsCount = $v["goodsCount"];
			if ($goodsCount <= 0) {
				return $this->bad("采购数量需要大于0");
			}
			$goodsPrice = $v["goodsPrice"];
			if ($goodsPrice < 0) {
				return $this->bad("采购单价不能是负数");
			}
			$goodsMoney = $v["goodsMoney"];
			$taxRate = $v["taxRate"];
			$tax = $v["tax"];
			$moneyWithTax = $v["moneyWithTax"];
			$memo = $v["memo"];
			
			$sql = "insert into t_po_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, pobill_id, tax_rate, tax, money_with_tax, pw_count, left_count,
						show_order, data_org, company_id, memo)
					values ('%s', now(), '%s', convert(%f, $fmt), %f,
						%f, '%s', %d, %f, %f, 0, convert(%f, $fmt), %d, '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
					$companyId, $memo);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
		$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $dealDate, $supplierId, 
				$dealAddress, $contact, $tel, $fax, $orgId, $bizUserId, $paymentType, $billMemo, 
				$loginUserId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 根据采购订单id查询采购订单
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getPOBillById($id) {
		$db = $this->db;
		
		$sql = "select ref, data_org, bill_status, company_id
				from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return [
					"ref" => $data[0]["ref"],
					"dataOrg" => $data[0]["data_org"],
					"billStatus" => $data[0]["bill_status"],
					"companyId" => $data[0]["company_id"]
			];
		} else {
			return null;
		}
	}

	/**
	 * 删除采购订单
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function deletePOBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getPOBillById($id);
		
		if (! $bill) {
			return $this->bad("要删除的采购订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus > 0) {
			return $this->bad("采购订单(单号：{$ref})已经审核，不能被删除");
		}
		
		$params["ref"] = $ref;
		
		$sql = "delete from t_po_bill_detail where pobill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_po_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		return null;
	}

	/**
	 * 获得采购订单的信息
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function poBillInfo($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = [];
		
		$bcDAO = new BizConfigDAO($db);
		$result["taxRate"] = $bcDAO->getTaxRate($companyId);
		
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
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
				$sql = "select p.id, p.goods_id, g.code, g.name, g.spec, 
							convert(p.goods_count, " . $fmt . ") as goods_count, 
							p.goods_price, p.goods_money,
							p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name, p.memo
						from t_po_bill_detail p, t_goods g, t_goods_unit u
						where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
						order by p.show_order";
				$items = [];
				$data = $db->query($sql, $id);
				
				foreach ( $data as $v ) {
					$items[] = [
							"goodsId" => $v["goods_id"],
							"goodsCode" => $v["code"],
							"goodsName" => $v["name"],
							"goodsSpec" => $v["spec"],
							"goodsCount" => $v["goods_count"],
							"goodsPrice" => $v["goods_price"],
							"goodsMoney" => $v["goods_money"],
							"taxRate" => $v["tax_rate"],
							"tax" => $v["tax"],
							"moneyWithTax" => $v["money_with_tax"],
							"unitName" => $v["unit_name"],
							"memo" => $v["memo"]
					];
				}
				
				$result["items"] = $items;
			}
		} else {
			// 新建采购订单
			$loginUserId = $params["loginUserId"];
			$result["bizUserId"] = $loginUserId;
			$result["bizUserName"] = $params["loginUserName"];
			
			$sql = "select o.id, o.full_name
					from t_org o, t_user u
					where o.id = u.org_id and u.id = '%s' ";
			$data = $db->query($sql, $loginUserId);
			if ($data) {
				$result["orgId"] = $data[0]["id"];
				$result["orgFullName"] = $data[0]["full_name"];
			}
			
			// 采购订单默认付款方式
			$bc = new BizConfigDAO($db);
			$result["paymentType"] = $bc->getPOBillDefaultPayment($params);
		}
		
		return $result;
	}

	/**
	 * 审核采购订单
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function commitPOBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$bill = $this->getPOBillById($id);
		if (! $bill) {
			return $this->bad("要审核的采购订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus > 0) {
			return $this->bad("采购订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$sql = "update t_po_bill
				set bill_status = 1000,
					confirm_user_id = '%s',
					confirm_date = now()
				where id = '%s' ";
		$rc = $db->execute($sql, $loginUserId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 取消审核采购订单
	 *
	 * @param array $params        	
	 * @return NULL|array
	 */
	public function cancelConfirmPOBill(& $params) {
		$db = $this->db;
		$id = $params["id"];
		
		$bill = $this->getPOBillById($id);
		if (! $bill) {
			return $this->bad("要取消审核的采购订单不存在");
		}
		
		$ref = $bill["ref"];
		$params["ref"] = $ref;
		
		$billStatus = $bill["billStatus"];
		if ($billStatus > 1000) {
			return $this->bad("采购订单(单号:{$ref})不能取消审核");
		}
		
		if ($billStatus == 0) {
			return $this->bad("采购订单(单号:{$ref})还没有审核，无需进行取消审核操作");
		}
		
		$sql = "select count(*) as cnt from t_po_pw where po_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("采购订单(单号:{$ref})已经生成了采购入库单，不能取消审核");
		}
		
		$sql = "update t_po_bill
				set bill_status = 0, confirm_user_id = null, confirm_date = null
				where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 查询采购订单的数据，用于生成PDF文件
	 *
	 * @param array $params        	
	 *
	 * @return NULL|array
	 */
	public function getDataForPDF($params) {
		$db = $this->db;
		
		$ref = $params["ref"];
		
		$sql = "select p.id, p.bill_status, p.goods_money, p.tax, p.money_with_tax,
					s.name as supplier_name, p.contact, p.tel, p.fax, p.deal_address,
					p.deal_date, p.payment_type, p.bill_memo, p.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					p.confirm_user_id, p.confirm_date, p.company_id
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id) 
					and (p.ref = '%s')";
		
		$data = $db->query($sql, $ref);
		if (! $data) {
			return null;
		}
		
		$v = $data[0];
		$id = $v["id"];
		$companyId = $v["company_id"];
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$result = [];
		
		$result["billStatus"] = $v["bill_status"];
		$result["supplierName"] = $v["supplier_name"];
		$result["goodsMoney"] = $v["goods_money"];
		$result["tax"] = $v["tax"];
		$result["moneyWithTax"] = $v["money_with_tax"];
		$result["dealDate"] = $this->toYMD($v["deal_date"]);
		$result["dealAddress"] = $v["deal_address"];
		$result["bizUserName"] = $v["biz_user_name"];
		
		$sql = "select p.id, g.code, g.name, g.spec, convert(p.goods_count, $fmt) as goods_count, 
					p.goods_price, p.goods_money,
					p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
		$items = [];
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$items[] = [
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"goodsCount" => $v["goods_count"],
					"unitName" => $v["unit_name"],
					"goodsPrice" => $v["goods_price"],
					"goodsMoney" => $v["goods_money"]
			];
		}
		
		$result["items"] = $items;
		
		return $result;
	}

	/**
	 * 采购订单执行的采购入库单信息
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function poBillPWBillList($params) {
		$db = $this->db;
		
		// id: 采购订单id
		$id = $params["id"];
		
		$sql = "select p.id, p.bill_status, p.ref, p.biz_dt, u1.name as biz_user_name, u2.name as input_user_name,
					p.goods_money, w.name as warehouse_name, s.name as supplier_name,
					p.date_created, p.payment_type
				from t_pw_bill p, t_warehouse w, t_supplier s, t_user u1, t_user u2,
					t_po_pw popw
				where (popw.po_id = '%s') and (popw.pw_id = p.id)
				and (p.warehouse_id = w.id) and (p.supplier_id = s.id)
				and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
				order by p.ref ";
		$data = $db->query($sql, $id);
		$result = [];
		
		foreach ( $data as $v ) {
			$billStatus = $v["bill_status"];
			$bs = "";
			if ($billStatus == 0) {
				$bs = "待入库";
			} else if ($billStatus == 1000) {
				$bs = "已入库";
			} else if ($billStatus == 2000) {
				$bs = "已退货";
			} else if ($billStatus == 9000) {
				// TODO 9000这个状态似乎并没有使用？？？
				$bs = "作废";
			}
			
			$result[] = [
					"id" => $v["id"],
					"ref" => $v["ref"],
					"bizDate" => $this->toYMD($v["biz_dt"]),
					"supplierName" => $v["supplier_name"],
					"warehouseName" => $v["warehouse_name"],
					"inputUserName" => $v["input_user_name"],
					"bizUserName" => $v["biz_user_name"],
					"billStatus" => $bs,
					"amount" => $v["goods_money"],
					"dateCreated" => $v["date_created"],
					"paymentType" => $v["payment_type"]
			];
		}
		
		return $result;
	}

	/**
	 * 关闭采购订单
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function closePOBill(&$params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select ref, bill_status
				from t_po_bill
				where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return $this->bad("要关闭的采购订单不存在");
		}
		
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		
		if ($billStatus >= 4000) {
			return $this->bad("采购订单已经被关闭");
		}
		
		// 检查该采购订单是否有生成的采购入库单，并且这些采购入库单是没有提交入库的
		// 如果存在这类采购入库单，那么该采购订单不能关闭。
		$sql = "select count(*) as cnt
				from t_pw_bill w, t_po_pw p
				where w.id = p.pw_id and p.po_id = '%s'
					and w.bill_status = 0 ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$info = "当前采购订单生成的入库单中还有没提交的<br/><br/>把这些入库单删除后，才能关闭采购订单";
			return $this->bad($info);
		}
		
		if ($billStatus < 1000) {
			return $this->bad("当前采购订单还没有审核，没有审核的采购订单不能关闭");
		}
		
		$newBillStatus = - 1;
		if ($billStatus == 1000) {
			// 当前订单只是审核了
			$newBillStatus = 4000;
		} else if ($billStatus == 2000) {
			// 部分入库
			$newBillStatus = 4001;
		} else if ($billStatus == 3000) {
			// 全部入库
			$newBillStatus = 4002;
		}
		
		if ($newBillStatus == - 1) {
			return $this->bad("当前采购订单的订单状态是不能识别的状态码：{$billStatus}");
		}
		
		$sql = "update t_po_bill
				set bill_status = %d
				where id = '%s' ";
		$rc = $db->execute($sql, $newBillStatus, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 取消关闭采购订单
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function cancelClosedPOBill(&$params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select ref, bill_status
				from t_po_bill
				where id = '%s' ";
		$data = $db->query($sql, $id);
		
		if (! $data) {
			return $this->bad("要关闭的采购订单不存在");
		}
		
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		
		if ($billStatus < 4000) {
			return $this->bad("采购订单没有被关闭，无需取消");
		}
		
		$newBillStatus = - 1;
		if ($billStatus == 4000) {
			$newBillStatus = 1000;
		} else if ($billStatus == 4001) {
			$newBillStatus = 2000;
		} else if ($billStatus == 4002) {
			$newBillStatus = 3000;
		}
		
		if ($newBillStatus == - 1) {
			return $this->bad("当前采购订单的订单状态是不能识别的状态码：{$billStatus}");
		}
		
		$sql = "update t_po_bill
				set bill_status = %d
				where id = '%s' ";
		$rc = $db->execute($sql, $newBillStatus, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 为使用Lodop打印准备数据
	 *
	 * @param array $params        	
	 */
	public function getPOBillDataForLodopPrint($params) {
		$db = $this->db;
		$result = [];
		
		$id = $params["id"];
		
		$sql = "select p.ref, p.bill_status, p.goods_money, p.tax, p.money_with_tax,
					s.name as supplier_name, p.contact, p.tel, p.fax, p.deal_address,
					p.deal_date, p.payment_type, p.bill_memo, p.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					p.confirm_user_id, p.confirm_date, p.company_id
				from t_po_bill p, t_supplier s, t_org o, t_user u1, t_user u2
				where (p.supplier_id = s.id) and (p.org_id = o.id)
					and (p.biz_user_id = u1.id) and (p.input_user_id = u2.id)
					and (p.id = '%s')";
		
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$v = $data[0];
		$result["ref"] = $v["ref"];
		$result["goodsMoney"] = $v["goods_money"];
		$result["tax"] = $v["tax"];
		$result["moneyWithTax"] = $v["money_with_tax"];
		$result["supplierName"] = $v["supplier_name"];
		$result["contact"] = $v["contact"];
		$result["tel"] = $v["tel"];
		$result["dealDate"] = $this->toYMD($v["deal_date"]);
		$result["dealAddress"] = $v["deal_address"];
		$result["billMemo"] = $v["bill_memo"];
		
		$result["printDT"] = date("Y-m-d H:i:s");
		
		$companyId = $v["company_id"];
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$sql = "select p.id, g.code, g.name, g.spec, convert(p.goods_count, $fmt) as goods_count,
				p.goods_price, p.goods_money,
				p.tax_rate, p.tax, p.money_with_tax, u.name as unit_name
				from t_po_bill_detail p, t_goods g, t_goods_unit u
				where p.pobill_id = '%s' and p.goods_id = g.id and g.unit_id = u.id
				order by p.show_order";
		$items = [];
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$items[] = [
					"goodsCode" => $v["code"],
					"goodsName" => $v["name"],
					"goodsSpec" => $v["spec"],
					"goodsCount" => $v["goods_count"],
					"unitName" => $v["unit_name"],
					"goodsPrice" => $v["goods_price"],
					"goodsMoney" => $v["goods_money"],
					"taxRate" => intval($v["tax_rate"]),
					"goodsMoneyWithTax" => $v["money_with_tax"]
			];
		}
		
		$result["items"] = $items;
		
		return $result;
	}
}