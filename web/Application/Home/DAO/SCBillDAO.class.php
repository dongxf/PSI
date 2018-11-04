<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 销售合同 DAO
 *
 * @author 李静波
 */
class SCBillDAO extends PSIBaseExDAO {

	/**
	 * 生成新的销售合同号
	 *
	 * @param string $companyId        	
	 *
	 * @return string
	 */
	private function genNewBillRef($companyId) {
		$db = $this->db;
		
		$bs = new BizConfigDAO($db);
		$pre = $bs->getSCBillRefPre($companyId);
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_sc_bill where ref like '%s' order by ref desc limit 1";
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
	 * 获得销售合同主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function scbillList($params) {
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
		
		$sql = "select s.id, s.ref, s.bill_status, c.name as customer_name,
					u.name as input_user_name, g.full_name as org_name,
					s.begin_dt, s.end_dt, s.goods_money, s.tax, s.money_with_tax,
					s.deal_date, s.deal_address, s.bill_memo, s.discount,
					u2.name as biz_user_name, s.biz_dt, s.confirm_user_id, s.confirm_date,
					s.date_created
				from t_sc_bill s, t_customer c, t_user u, t_org g, t_user u2
				where (s.customer_id = c.id) and (s.input_user_id = u.id) 
					and (s.org_id = g.id) and (s.biz_user_id = u2.id) ";
		
		$queryParams = [];
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_CONTRACT, "s", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		if ($ref) {
			$sql .= " and (s.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($billStatus != - 1) {
			$sql .= " and (s.bill_status = %d) ";
			$queryParams[] = $billStatus;
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
		$sql .= " order by s.deal_date desc, s.ref desc
				  limit %d , %d";
		$queryParams[] = $start;
		$queryParams[] = $limit;
		$data = $db->query($sql, $queryParams);
		
		$result = [];
		foreach ( $data as $v ) {
			$item = [
					"id" => $v["id"],
					"billStatus" => $v["bill_status"],
					"ref" => $v["ref"],
					"customerName" => $v["customer_name"],
					"orgName" => $v["org_name"],
					"beginDT" => $this->toYMD($v["begin_dt"]),
					"endDT" => $this->toYMD($v["end_dt"]),
					"goodsMoney" => $v["goods_money"],
					"tax" => $v["tax"],
					"moneyWithTax" => $v["money_with_tax"],
					"dealDate" => $this->toYMD($v["deal_date"]),
					"dealAddress" => $v["deal_address"],
					"discount" => $v["discount"],
					"bizUserName" => $v["biz_user_name"],
					"bizDT" => $this->toYMD($v["biz_dt"]),
					"billMemo" => $v["bill_memo"],
					"inputUserName" => $v["input_user_name"],
					"dateCreated" => $v["date_created"]
			];
			
			$confirmUserId = $v["confirm_user_id"];
			if ($confirmUserId) {
				$sql = "select name from t_user where id = '%s' ";
				$d = $db->query($sql, $confirmUserId);
				if ($d) {
					$item["confirmUserName"] = $d[0]["name"];
					$item["confirmDate"] = $v["confirm_date"];
				}
			}
			
			$result[] = $item;
		}
		
		$sql = "select count(*) as cnt
				from t_sc_bill s, t_customer c, t_user u, t_org g, t_user u2
				where (s.customer_id = c.id) and (s.input_user_id = u.id)
					and (s.org_id = g.id) and (s.biz_user_id = u2.id) ";
		$queryParams = [];
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::SALE_CONTRACT, "s", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		if ($ref) {
			$sql .= " and (s.ref like '%s') ";
			$queryParams[] = "%{$ref}%";
		}
		if ($billStatus != - 1) {
			$sql .= " and (s.bill_status = %d) ";
			$queryParams[] = $billStatus;
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
		
		$data = $db->query($sql, $queryParams);
		$cnt = $data[0]["cnt"];
		
		return [
				"dataList" => $result,
				"totalCount" => $cnt
		];
	}

	/**
	 * 销售合同详情
	 */
	public function scBillInfo($params) {
		$db = $this->db;
		
		// 销售合同id
		$id = $params["id"];
		$result = [];
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$cs = new BizConfigDAO($db);
		$result["taxRate"] = $cs->getTaxRate($companyId);
		
		$dataScale = $cs->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		if ($id) {
			// 编辑或查看
		} else {
			// 新建
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
		}
		
		return $result;
	}

	/**
	 * 新增销售合同
	 *
	 * @param array $params        	
	 * @return array|null
	 */
	public function addSCBill(& $bill) {
		$db = $this->db;
		
		$companyId = $bill["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$dataOrg = $bill["dataOrg"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		
		$loginUserId = $bill["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$customerId = $bill["customerId"];
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("甲方客户不存在");
		}
		
		$beginDT = $bill["beginDT"];
		if (! $this->dateIsValid($beginDT)) {
			return $this->bad("合同开始日期不正确");
		}
		$endDT = $bill["endDT"];
		if (! $this->dateIsValid($endDT)) {
			return $this->bad("合同结束日期不正确");
		}
		
		$orgId = $bill["orgId"];
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			return $this->bad("乙方组织机构不存在");
		}
		
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			return $this->bad("交货日期不正确");
		}
		
		$bizDT = $bill["bizDT"];
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("合同签订日期不正确");
		}
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("业务员不存在");
		}
		
		$dealAddress = $bill["dealAddress"];
		$discount = intval($bill["discount"]);
		if ($discount < 0 || $discount > 100) {
			$discount = 100;
		}
		
		$billMemo = $bill["billMemo"];
		$qualityClause = $bill["qualityClause"];
		$insuranceClause = $bill["insuranceClause"];
		$transportClause = $bill["transportClause"];
		$otherClause = $bill["otherClause"];
		
		$items = $bill["items"];
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		$id = $this->newId();
		$ref = $this->genNewBillRef($companyId);
		
		// 主表
		$sql = "insert into t_sc_bill (id, ref, customer_id, org_id, biz_user_id,
					biz_dt, input_user_id, date_created, bill_status, goods_money,
					tax, money_with_tax, deal_date, deal_address, bill_memo, 
					data_org, company_id, begin_dt, end_dt, discount,
					quality_clause, insurance_clause, transport_clause, other_clause)
				values ('%s', '%s', '%s', '%s', '%s',
					'%s', '%s', now(), 0, 0,
					0, 0, '%s', '%s', '%s',
					'%s', '%s', '%s', '%s', %d,
					'%s', '%s', '%s', '%s')";
		$rc = $db->execute($sql, $id, $ref, $customerId, $orgId, $bizUserId, $bizDT, $loginUserId, 
				$dealDate, $dealAddress, $billMemo, $dataOrg, $companyId, $beginDT, $endDT, 
				$discount, $qualityClause, $insuranceClause, $transportClause, $otherClause);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细表
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
			
			$sql = "insert into t_sc_bill_detail(id, date_created, goods_id, goods_count, goods_money,
						goods_price, scbill_id, tax_rate, tax, money_with_tax, so_count, left_count,
						show_order, data_org, company_id, memo, discount)
					values ('%s', now(), '%s', convert(%f, $fmt), %f,
						%f, '%s', %d, %f, %f, 0, convert(%f, $fmt), %d, '%s', '%s', '%s', %d)";
			$rc = $db->execute($sql, $this->newId(), $goodsId, $goodsCount, $goodsMoney, 
					$goodsPrice, $id, $taxRate, $tax, $moneyWithTax, $goodsCount, $i, $dataOrg, 
					$companyId, $memo, $discount);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 同步主表的金额合计字段
		$sql = "select sum(goods_money) as sum_goods_money, sum(tax) as sum_tax,
					sum(money_with_tax) as sum_money_with_tax
				from t_sc_bill_detail
				where scbill_id = '%s' ";
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
		
		$sql = "update t_sc_bill
				set goods_money = %f, tax = %f, money_with_tax = %f
				where id = '%s' ";
		$rc = $db->execute($sql, $sumGoodsMoney, $sumTax, $sumMoneyWithTax, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		$bill["id"] = $id;
		$bill["ref"] = $ref;
		return null;
	}

	/**
	 * 编辑销售合同
	 *
	 * @param array $params        	
	 * @return array|null
	 */
	public function updateSCBill(& $bill) {
		$db = $this->db;
		
		return $this->todo();
	}

	/**
	 * 销售合同商品明细
	 */
	public function scBillDetailList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->emptyResult();
		}
		
		$bcDAO = new BizConfigDAO($db);
		$dataScale = $bcDAO->getGoodsCountDecNumber($companyId);
		$fmt = "decimal(19, " . $dataScale . ")";
		
		// 销售合同主表id
		$id = $params["id"];
		
		$sql = "select quality_clause, insurance_clause, transport_clause, other_clause
				from t_sc_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->emptyResult();
		}
		
		$v = $data[0];
		$result = [
				"qualityClause" => $v["quality_clause"],
				"insuranceClause" => $v["insurance_clause"],
				"transportClause" => $v["transport_clause"],
				"otherClause" => $v["other_clause"]
		];
		
		$sql = "select s.id, g.code, g.name, g.spec, convert(s.goods_count, " . $fmt . ") as goods_count,
					s.goods_price, s.goods_money,
					s.tax_rate, s.tax, s.money_with_tax, u.name as unit_name,
					convert(s.so_count, " . $fmt . ") as so_count,
					convert(s.left_count, " . $fmt . ") as left_count, s.memo
				from t_sc_bill_detail s, t_goods g, t_goods_unit u
				where s.scbill_id = '%s' and s.goods_id = g.id and g.unit_id = u.id
				order by s.show_order";
		$items = [];
		$data = $db->query($sql, $id);
		
		foreach ( $data as $v ) {
			$items[] = [
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
					"soCount" => $v["so_count"],
					"leftCount" => $v["left_count"],
					"memo" => $v["memo"]
			];
		}
		
		$result["items"] = $items;
		
		return $result;
	}
}