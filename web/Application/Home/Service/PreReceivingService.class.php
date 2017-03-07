<?php

namespace Home\Service;

use Home\DAO\PreReceivingDAO;

/**
 * 预收款Service
 *
 * @author 李静波
 */
class PreReceivingService extends PSIBaseExService {
	private $LOG_CATEGORY = "预收款管理";

	public function addPreReceivingInfo() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		return array(
				"bizUserId" => $this->getLoginUserId(),
				"bizUserName" => $this->getLoginUserName()
		);
	}

	public function returnPreReceivingInfo() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		return array(
				"bizUserId" => $this->getLoginUserId(),
				"bizUserName" => $this->getLoginUserName()
		);
	}

	/**
	 * 收预收款
	 */
	public function addPreReceiving($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$params["companyId"] = $this->getCompanyId();
		$params["loginUserId"] = $this->getLoginUserId();
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new PreReceivingDAO($db);
		$rc = $dao->addPreReceiving($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$customerName = $params["customerName"];
		$inMoney = $params["inMoney"];
		$log = "收取客户[{$customerName}]预收款：{$inMoney}元";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 退还预收款
	 */
	public function returnPreReceiving($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$params["companyId"] = $this->getCompanyId();
		$params["loginUserId"] = $this->getLoginUserId();
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new PreReceivingDAO($db);
		$rc = $dao->returnPreReceiving($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$customerName = $params["customerName"];
		$outMoney = $params["outMoney"];
		$log = "退还客户[{$customerName}]预收款：{$outMoney}元";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function prereceivingList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$categoryId = $params["categoryId"];
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$db = M();
		$sql = "select r.id, c.id as customer_id, c.code, c.name,
					r.in_money, r.out_money, r.balance_money
				from t_pre_receiving r, t_customer c
				where r.customer_id = c.id and c.category_id = '%s'
					and r.company_id = '%s'
				limit %d , %d
				";
		$data = $db->query($sql, $categoryId, $companyId, $start, $limit);
		
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["customerId"] = $v["customer_id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["inMoney"] = $v["in_money"];
			$result[$i]["outMoney"] = $v["out_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
		}
		
		$sql = "select count(*) as cnt
				from t_pre_receiving r, t_customer c
				where r.customer_id = c.id and c.category_id = '%s'
					and r.company_id = '%s'
				";
		$data = $db->query($sql, $categoryId, $companyId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}

	public function prereceivingDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$customerId = $params["customerId"];
		$dtFrom = $params["dtFrom"];
		$dtTo = $params["dtTo"];
		
		$us = new UserService();
		$companyId = $us->getCompanyId();
		
		$db = M();
		$sql = "select d.id, d.ref_type, d.ref_number, d.in_money, d.out_money, d.balance_money,
					d.biz_date, d.date_created,
					u1.name as biz_user_name, u2.name as input_user_name
				from t_pre_receiving_detail d, t_user u1, t_user u2
				where d.customer_id = '%s' and d.biz_user_id = u1.id and d.input_user_id = u2.id
					and (d.biz_date between '%s' and '%s')
					and d.company_id = '%s'
				order by d.date_created
				limit %d , %d
				";
		$data = $db->query($sql, $customerId, $dtFrom, $dtTo, $companyId, $start, $limit);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["refType"] = $v["ref_type"];
			$result[$i]["refNumber"] = $v["ref_number"];
			$result[$i]["inMoney"] = $v["in_money"];
			$result[$i]["outMoney"] = $v["out_money"];
			$result[$i]["balanceMoney"] = $v["balance_money"];
			$result[$i]["bizDT"] = $this->toYMD($v["biz_date"]);
			$result[$i]["dateCreated"] = $v["date_created"];
			$result[$i]["bizUserName"] = $v["biz_user_name"];
			$result[$i]["inputUserName"] = $v["input_user_name"];
		}
		
		$sql = "select count(*) as cnt
				from t_pre_receiving_detail d, t_user u1, t_user u2
				where d.customer_id = '%s' and d.biz_user_id = u1.id and d.input_user_id = u2.id
					and (d.biz_date between '%s' and '%s')
					and d.company_id = '%s'
				";
		
		$data = $db->query($sql, $customerId, $dtFrom, $dtTo, $companyId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}