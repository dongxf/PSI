<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 销售订单 DAO
 *
 * @author 李静波
 */
class SOBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的销售订单号
	 *
	 * @param string $companyId        	
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getSOBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_so_bill where ref like '%s' order by ref desc limit 1";
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
	 * 获得销售订单主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function sobillList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$billStatus = $params["billStatus"];
		$ref = $params["ref"];
		$fromDT = $params["fromDT"];
		$toDT = $params["toDT"];
		$customerId = $params["customerId"];
		$receivingType = $params["receivingType"];
		
		$queryParams = array();
		
		$result = array();
		$sql = "select s.id, s.ref, s.bill_status, s.goods_money, s.tax, s.money_with_tax,
					c.name as customer_name, s.contact, s.tel, s.fax, s.deal_address,
					s.deal_date, s.receiving_type, s.bill_memo, s.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					s.confirm_user_id, s.confirm_date
				from t_so_bill s, t_customer c, t_org o, t_user u1, t_user u2
				where (s.customer_id = c.id) and (s.org_id = o.id)
					and (s.biz_user_id = u1.id) and (s.input_user_id = u2.id) ";
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_ORDER, "s", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		if ($billStatus != - 1) {
			$sql .= " and (s.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (s.ref like '%s') ";
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (s.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (s.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (s.customer_id = '%s')";
			$queryParams[] = $customerId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (s.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		$sql .= " order by s.deal_date desc, s.ref desc
				  limit %d , %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["ref"] = $v["ref"];
			$result[$i]["billStatus"] = $v["bill_status"];
			$result[$i]["dealDate"] = $this->toYMD($v["deal_date"]);
			$result[$i]["dealAddress"] = $v["deal_address"];
			$result[$i]["customerName"] = $v["customer_name"];
			$result[$i]["contact"] = $v["contact"];
			$result[$i]["tel"] = $v["tel"];
			$result[$i]["fax"] = $v["fax"];
			$result[$i]["goodsMoney"] = $v["goods_money"];
			$result[$i]["tax"] = $v["tax"];
			$result[$i]["moneyWithTax"] = $v["money_with_tax"];
			$result[$i]["receivingType"] = $v["receiving_type"];
			$result[$i]["billMemo"] = $v["bill_memo"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["orgName"] = $v["org_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
			$result[$i]["dateCreated"] = $v["date_created"];
			
			$confirmUserId = $v["confirm_user_id"];
			if ($confirmUserId) {
				$sql = "select name from t_user where id = '%s' ";
				$d = $db->query($sql, $confirmUserId);
				if ($d) {
					$result[$i]["confirmUserName"] = $d[0]["name"];
					$result[$i]["confirmDate"] = $v["confirm_date"];
				}
			}
		}
		
		$sql = "select count(*) as cnt
				from t_so_bill s, t_customer c, t_org o, t_user u1, t_user u2
				where (s.customer_id = c.id) and (s.org_id = o.id)
					and (s.biz_user_id = u1.id) and (s.input_user_id = u2.id)
				";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_ORDER, "s", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		if ($billStatus != - 1) {
			$sql .= " and (s.bill_status = %d) ";
			$queryParams[] = $billStatus;
		}
		if ($ref) {
			$sql .= " and (s.ref like '%s') ";
			$queryParams[] = "%$ref%";
		}
		if ($fromDT) {
			$sql .= " and (s.deal_date >= '%s')";
			$queryParams[] = $fromDT;
		}
		if ($toDT) {
			$sql .= " and (s.deal_date <= '%s')";
			$queryParams[] = $toDT;
		}
		if ($customerId) {
			$sql .= " and (s.customer_id = '%s')";
			$queryParams[] = $customerId;
		}
		if ($receivingType != - 1) {
			$sql .= " and (s.receiving_type = %d) ";
			$queryParams[] = $receivingType;
		}
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	/**
	 * 获得某个销售订单的明细信息
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function soBillDetailList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		// id:销售订单id
		$id = $params["id"];
		
		$sql = "select s.id, g.code, g.name, g.spec, convert(s.goods_count, " . $fmt . ") as goods_count,
					s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name,
					convert(s.ws_count, " . $fmt . ") as ws_count,
					convert(s.left_count, " . $fmt . ") as left_count, s.memo
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$item = array(
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
					"wsCount" => $v["ws_count"],
					"leftCount" => $v["left_count"],
					"memo" => $v["memo"]
			);
			$result[] = $item;
		}
		
		return $result;
	}

	/**
	 * 新建销售订单
	 *
	 * @param array $bill        	
	 * @return null|array
	 */
	public function addSOBill(& $bill) {
		$db = $this->db;
		
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$customerId = $bill["customerId"];
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("客户不存在");
		}
		
		$orgId = $bill["orgId"];
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			return $this->bad("组织机构不存在");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$receivingType = $bill["receivingType"];
		$contact = $bill["contact"];
		$tel = $bill["tel"];
		$fax = $bill["fax"];
		$dealAddress = $bill["dealAddress"];
		$billMemo = $bill["billMemo"];
		
		$items = $bill["items"];
		
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->bad("所属公司不存在");
		}
		
		$dataOrg = $bill["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		
		// 主表
		$sql = "insert into t_so_bill(id, ref, bill_status, deal_date, biz_dt, org_id, biz_user_id,
					goods_money, tax, money_with_tax, input_user_id, customer_id, contact, tel, fax,
					deal_address, bill_memo, receiving_type, date_created, data_org, company_id)
				values ('%s', '%s', 0, '%s', '%s', '%s', '%s',
					0, 0, 0, '%s', '%s', '%s', '%s', '%s',
					'%s', '%s', %d, now(), '%s', '%s')";
		$rc = $db->execute($sql, $id, $ref, $dealDate, $dealDate, $orgId, $bizUserId, $loginUserId, 
				$customerId, $contact, $tel, $fax, $dealAddress, $billMemo, $receivingType, $dataOrg, 
				$companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
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
			$memo = $v["memo"];
			
			$sql = "insert into t_so_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, sobill_id, tax_rate, tax, money_with_tax, ws_count, left_count,
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
				from t_so_bill_detail
				where sobill_id = '%s' ";
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
		
		$sql = "update t_so_bill
				set goods_money = %f, tax = %f, money_with_tax = %f
				where id = '%s' ";
		$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		
		return null;
	}

	/**
	 * 通过销售订单id查询销售订单
	 *
	 * @param string $id        	
	 * @return array|NULL
	 */
	public function getSOBillById($id) {
		$db = $this->db;
		
		$sql = "select ref, data_org, bill_status, company_id from t_so_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		} else {
			return array(
					"ref" => $data[0]["ref"],
					"dataOrg" => $data[0]["data_org"],
					"billStatus" => $data[0]["bill_status"],
					"companyId" => $data[0]["company_id"]
			);
		}
	}

	/**
	 * 编辑销售订单
	 *
	 * @param array $bill        	
	 * @return null|array
	 */
	public function updateSOBill(& $bill) {
		$db = $this->db;
		
		$id = $bill["id"];
		
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$customerId = $bill["customerId"];
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("客户不存在");
		}
		
		$orgId = $bill["orgId"];
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			return $this->bad("组织机构不存在");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$receivingType = $bill["receivingType"];
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
		
		$oldBill = $this->getSOBillById($id);
		
		if (! $oldBill) {
			return $this->bad("要编辑的销售订单不存在");
		}
		$ref = $oldBill["ref"];
		$dataOrg = $oldBill["dataOrg"];
		$companyId = $oldBill["companyId"];
		$billStatus = $oldBill["billStatus"];
		if ($billStatus != 0) {
			return $this->bad("当前销售订单已经审核，不能再编辑");
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$sql = "delete from t_so_bill_detail where sobill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
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
			$memo = $v["memo"];
			
			$sql = "insert into t_so_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, sobill_id, tax_rate, tax, money_with_tax, ws_count, left_count,
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
				from t_so_bill_detail
				where sobill_id = '%s' ";
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
		
		$sql = "update t_so_bill
				set goods_money = %f, tax = %f, money_with_tax = %f,
					deal_date = '%s', customer_id = '%s',
					deal_address = '%s', contact = '%s', tel = '%s', fax = '%s',
					org_id = '%s', biz_user_id = '%s', receiving_type = %d,
					bill_memo = '%s', input_user_id = '%s', date_created = now()
				where id = '%s' ";
		$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $dealDate, $customerId, 
				$dealAddress, $contact, $tel, $fax, $orgId, $bizUserId, $receivingType, $billMemo, 
				$loginUserId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$bill["ref"] = $ref;
		
		return null;
	}

	/**
	 * 删除销售订单
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function deleteSOBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getSOBillById($id);
		
		if (! $bill) {
			return $this->bad("要删除的销售订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus > 0) {
			return $this->bad("销售订单(单号：{$ref})已经审核，不能被删除");
		}
		
		$sql = "delete from t_so_bill_detail where sobill_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_so_bill where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		return null;
	}

	/**
	 * 获得销售订单的信息
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function soBillInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$result = [];
		
		$cs = new BizConfigDAO($db);
		$result["taxRate"] = $cs->getTaxRate($companyId);
		
		$dataScale = $cs->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		if ($id) {
			// 编辑销售订单
			$sql = "select s.ref, s.deal_date, s.deal_address, s.customer_id,
						c.name as customer_name, s.contact, s.tel, s.fax,
						s.org_id, o.full_name, s.biz_user_id, u.name as biz_user_name,
						s.receiving_type, s.bill_memo, s.bill_status
					from t_so_bill s, t_customer c, t_user u, t_org o
					where s.id = '%s' and s.customer_Id = c.id
						and s.biz_user_id = u.id
						and s.org_id = o.id";
			$data = $db->query($sql, $id);
			if ($data) {
				$v = $data[0];
				$result["ref"] = $v["ref"];
				$result["dealDate"] = $this->toYMD($v["deal_date"]);
				$result["dealAddress"] = $v["deal_address"];
				$result["customerId"] = $v["customer_id"];
				$result["customerName"] = $v["customer_name"];
				$result["contact"] = $v["contact"];
				$result["tel"] = $v["tel"];
				$result["fax"] = $v["fax"];
				$result["orgId"] = $v["org_id"];
				$result["orgFullName"] = $v["full_name"];
				$result["bizUserId"] = $v["biz_user_id"];
				$result["bizUserName"] = $v["biz_user_name"];
				$result["receivingType"] = $v["receiving_type"];
				$result["billMemo"] = $v["bill_memo"];
				$result["billStatus"] = $v["bill_status"];
				
				// 明细表
				$sql = "select s.id, s.goods_id, g.code, g.name, g.spec, 
							convert(s.goods_count, " . $fmt . ") as goods_count, s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name, s.memo
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
				$items = array();
				$data = $db->query($sql, $id);
				
				foreach ( $data as $v ) {
					$item = array(
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
					);
					$items[] = $item;
				}
				
				$result["items"] = $items;
			}
		} else {
			// 新建销售订单
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
			
			// 默认收款方式
			$bc = new BizConfigDAO($db);
			$result["receivingType"] = $bc->getSOBillDefaultReceving($companyId);
		}
		
		return $result;
	}

	/**
	 * 审核销售订单
	 *
	 * @param array $params        	
	 * @return null|array
	 */
	public function commitSOBill(& $params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$id = $params["id"];
		
		$bill = $this->getSOBillById($id);
		
		if (! $bill) {
			return $this->bad("要审核的销售订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus > 0) {
			return $this->bad("销售订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$sql = "update t_so_bill
				set bill_status = 1000,
					confirm_user_id = '%s',
					confirm_date = now()
				where id = '%s' ";
		$rc = $db->execute($sql, $loginUserId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		return null;
	}

	/**
	 * 取消销售订单审核
	 */
	public function cancelConfirmSOBill(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$bill = $this->getSOBillById($id);
		
		if (! $bill) {
			return $this->bad("要取消审核的销售订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus == 0) {
			return $this->bad("销售订单(单号:{$ref})还没有审核，无需取消审核操作");
		}
		if ($billStatus > 1000) {
			return $this->bad("销售订单(单号:{$ref})不能取消审核");
		}
		
		$sql = "select count(*) as cnt from t_so_ws where so_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("销售订单(单号:{$ref})已经生成了销售出库单，不能取消审核");
		}
		
		$sql = "update t_so_bill
				set bill_status = 0, confirm_user_id = null, confirm_date = null
				where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["ref"] = $ref;
		
		// 操作成功
		return null;
	}

	/**
	 * 销售订单生成pdf文件
	 */
	public function getDataForPDF($params) {
		$ref = $params["ref"];
		
		$db = $this->db;
		$sql = "select s.id, s.bill_status, s.goods_money, s.tax, s.money_with_tax,
					c.name as customer_name, s.contact, s.tel, s.fax, s.deal_address,
					s.deal_date, s.receiving_type, s.bill_memo, s.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					s.confirm_user_id, s.confirm_date, s.company_id
				from t_so_bill s, t_customer c, t_org o, t_user u1, t_user u2
				where (s.customer_id = c.id) and (s.org_id = o.id)
					and (s.biz_user_id = u1.id) and (s.input_user_id = u2.id) 
					and (s.ref = '%s')";
		$data = $db->query($sql, $ref);
		if (! $data) {
			return null;
		}
		
		$id = $data[0]["id"];
		
		$companyId = $data[0]["company_id"];
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$bill["bizDT"] = $this->toYMD($data[0]["bizdt"]);
		$bill["customerName"] = $data[0]["customer_name"];
		$bill["warehouseName"] = $data[0]["warehouse_name"];
		$bill["bizUserName"] = $data[0]["biz_user_name"];
		$bill["saleMoney"] = $data[0]["goods_money"];
		$bill["dealAddress"] = $data[0]["deal_address"];
		
		// 明细表
		$sql = "select s.id, g.code, g.name, g.spec, convert(s.goods_count, $fmt) as goods_count, 
					s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
		$data = $db->query($sql, $id);
		$items = array();
		foreach ( $data as $i => $v ) {
			$items[$i]["goodsCode"] = $v["code"];
			$items[$i]["goodsName"] = $v["name"];
			$items[$i]["goodsSpec"] = $v["spec"];
			$items[$i]["unitName"] = $v["unit_name"];
			$items[$i]["goodsCount"] = $v["goods_count"];
			$items[$i]["goodsPrice"] = $v["goods_price"];
			$items[$i]["goodsMoney"] = $v["goods_money"];
		}
		$bill["items"] = $items;
		
		return $bill;
	}

	/**
	 * 获得打印销售订单的数据
	 *
	 * @param array $params        	
	 */
	public function getSOBillDataForLodopPrint($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "select s.ref, s.bill_status, s.goods_money, s.tax, s.money_with_tax,
					c.name as customer_name, s.contact, s.tel, s.fax, s.deal_address,
					s.deal_date, s.receiving_type, s.bill_memo, s.date_created,
					o.full_name as org_name, u1.name as biz_user_name, u2.name as input_user_name,
					s.confirm_user_id, s.confirm_date, s.company_id, s.deal_date
				from t_so_bill s, t_customer c, t_org o, t_user u1, t_user u2
				where (s.customer_id = c.id) and (s.org_id = o.id)
					and (s.biz_user_id = u1.id) and (s.input_user_id = u2.id)
					and (s.id = '%s')";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		}
		
		$companyId = $data[0]["company_id"];
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$bill["ref"] = $data[0]["ref"];
		$bill["bizDT"] = $this->toYMD($data[0]["bizdt"]);
		$bill["customerName"] = $data[0]["customer_name"];
		$bill["bizUserName"] = $data[0]["biz_user_name"];
		$bill["saleMoney"] = $data[0]["goods_money"];
		$bill["dealAddress"] = $data[0]["deal_address"];
		$bill["dealDate"] = $this->toYMD($data[0]["deal_date"]);
		$bill["tel"] = $data[0]["tel"];
		$bill["billMemo"] = $data[0]["bill_memo"];
		$bill["goodsMoney"] = $data[0]["goods_money"];
		$bill["moneyWithTax"] = $data[0]["money_with_tax"];
		
		$bill["printDT"] = date("Y-m-d H:i:s");
		
		// 明细表
		$sql = "select s.id, g.code, g.name, g.spec, convert(s.goods_count, $fmt) as goods_count,
					s.goods_price, s.goods_money, s.memo,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
		$data = $db->query($sql, $id);
		$items = array();
		foreach ( $data as $i => $v ) {
			$items[$i]["goodsCode"] = $v["code"];
			$items[$i]["goodsName"] = $v["name"];
			$items[$i]["goodsSpec"] = $v["spec"];
			$items[$i]["unitName"] = $v["unit_name"];
			$items[$i]["goodsCount"] = $v["goods_count"];
			$items[$i]["goodsPrice"] = $v["goods_price"];
			$items[$i]["goodsMoney"] = $v["goods_money"];
			$items[$i]["taxRate"] = intval($v["tax_rate"]);
			$items[$i]["goodsMoneyWithTax"] = $v["money_with_tax"];
			$items[$i]["memo"] = $v["memo"];
		}
		$bill["items"] = $items;
		
		return $bill;
	}
}