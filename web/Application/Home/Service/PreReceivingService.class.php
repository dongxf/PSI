<?php

namespace Home\Service;

/**
 * 预收款Service
 *
 * @author 李静波
 */
class PreReceivingService extends PSIBaseService {

	public function addPreReceivingInfo() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		
		return array(
				"bizUserId" => $us->getLoginUserId(),
				"bizUserName" => $us->getLoginUserName()
		);
	}

	public function addPreReceiving($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$customerId = $params["customerId"];
		$bizUserId = $params["bizUserId"];
		$bizDT = $params["bizDT"];
		$inMoney = $params["inMoney"];
		
		$db = M();
		
		// 检查客户
		$cs = new CustomerService();
		if (! $cs->customerExists($customerId, $db)) {
			return $this->bad("客户不存在，无法预收款");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		// 检查收款人是否存在
		$us = new UserService();
		if (! $us->userExists($bizUserId, $db)) {
			return $this->bad("收款人不存在");
		}
		
		$inMoney = floatval($inMoney);
		if ($inMoney <= 0) {
			return $this->bad("收款金额需要是正数");
		}
		
		$idGen = new IdGenService();
		
		$db->startTrans();
		try {
			$sql = "select in_money, balance_money from t_pre_receiving
					where customer_id = '%s' ";
			$data = $db->query($sql, $customerId);
			if (! $data) {
				// 总账
				$sql = "insert into t_pre_receiving(id, customer_id, in_money, balance_money)
						values ('%s', '%s', %f, %f)";
				$rc = $db->execute($sql, $idGen->newId(), $customerId, $inMoney, $inMoney);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 明细账
				$sql = "insert into t_pre_receiving_detail(id, customer_id, in_money, balance_money, date_created,
							ref_number, ref_type, biz_user_id, input_user_id)
						values('%s', '%s', %f, %f, now(), '', '收预收款', '%s', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $customerId, $inMoney, $inMoney, 
						$bizUserId, $us->getLoginUserId());
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
			} else {
				$totalInMoney = $data[0]["in_money"];
				$totalBalanceMoney = $data[0]["balance_money"];
				if (! $totalInMoney) {
					$totalInMoney = 0;
				}
				if (! $totalBalanceMoney) {
					$totalBalanceMoney = 0;
				}
				
				$totalInMoney += $inMoney;
				$totalBalanceMoney += $inMoney;
				// 总账
				$sql = "update t_pre_receiving
						set in_money = %f, balance_money = %f
						where customer_id = '%s' ";
				$rc = $db->execute($sql, $totalInMoney, $totalBalanceMoney, $customerId);
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
				
				// 明细账
				$sql = "insert into t_pre_receiving_detail(id, customer_id, in_money, balance_money, date_created,
							ref_number, ref_type, biz_user_id, input_user_id)
						values('%s', '%s', %f, %f, now(), '', '收预收款', '%s', '%s')";
				$rc = $db->execute($sql, $idGen->newId(), $customerId, $inMoney, $totalBalanceMoney, 
						$bizUserId, $us->getLoginUserId());
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
			}
			
			$db->commit();
		} catch ( Exception $e ) {
			$db->rollback();
			return $this->sqlError();
		}
		
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
		
		$db = M();
		$sql = "select r.id, c.id as customer_id, c.code, c.name,
					r.in_money, r.out_money, r.balance_money
				from t_pre_receiving r, t_customer c
				where r.customer_id = c.id and c.category_id = '%s'
				limit %d , %d
				";
		$data = $db->query($sql, $categoryId, $start, $limit);
		
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
				";
		$data = $db->query($sql, $categoryId);
		$cnt = $data[0]["cnt"];
		
		return array(
				"dataList" => $result,
				"totalCount" => $cnt
		);
	}
}