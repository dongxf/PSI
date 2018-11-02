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
					u2.name as biz_user_name, s.biz_dt, s.confirm_user_id, s.confirm_date
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
	public function addSCBill(& $params) {
		$db = $this->db;
		
		return $this->todo();
	}

	/**
	 * 编辑销售合同
	 * 
	 * @param array $params        	
	 * @return array|null
	 */
	public function updateSCBill(& $params) {
		$db = $this->db;
		
		return $this->todo();
	}
}