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
		
		// id:销售订单id
		$id = $params["id"];
		
		$sql = "select s.id, g.code, g.name, g.spec, s.goods_count, s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name
				from t_so_bill_detail s, t_goods g, t_goods_unit u
				where s.sobill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
		$result = array();
		$data = $db->query($sql, $id);
		
		foreach ( $data as $i => $v ) {
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
			
			$sql = "insert into t_so_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, sobill_id, tax_rate, tax, money_with_tax, ws_count, left_count,
						show_order, data_org, company_id)
					values ('%s', now(), '%s', %d, %f,
						%f, '%s', %d, %f, %f, 0, %d, %d, '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
					$companyId);
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
			
			$sql = "insert into t_so_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, sobill_id, tax_rate, tax, money_with_tax, ws_count, left_count,
						show_order, data_org, company_id)
					values ('%s', now(), '%s', %d, %f,
						%f, '%s', %d, %f, %f, 0, %d, %d, '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
					$companyId);
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
}