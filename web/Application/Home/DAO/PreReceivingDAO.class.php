<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 预收款 DAO
 *
 * @author 李静波
 */
class PreReceivingDAO extends PSIBaseExDAO {

	/**
	 * 收预收款
	 */
	public function addPreReceiving(& $params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		$loginUserId = $params["loginUserId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$customerId = $params["customerId"];
		$bizUserId = $params["bizUserId"];
		$bizDT = $params["bizDT"];
		$inMoney = $params["inMoney"];
		
		// 检查客户
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("客户不存在，无法预收款");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		// 检查收款人是否存在
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("收款人不存在");
		}
		
		$inMoney = floatval($inMoney);
		if ($inMoney <= 0) {
			return $this->bad("收款金额需要是正数");
		}
		
		$sql = "select in_money, balance_money from t_pre_receiving
				where customer_id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $customerId, $companyId);
		if (! $data) {
			// 总账
			$sql = "insert into t_pre_receiving(id, customer_id, in_money, balance_money, company_id)
					values ('%s', '%s', %f, %f, '%s')";
			$rc = $db->execute($sql, $this->newId(), $customerId, $inMoney, $inMoney, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 明细账
			$sql = "insert into t_pre_receiving_detail(id, customer_id, in_money, balance_money, date_created,
						ref_number, ref_type, biz_user_id, input_user_id, biz_date, company_id)
					values('%s', '%s', %f, %f, now(), '', '收预收款', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $customerId, $inMoney, $inMoney, $bizUserId, 
					$loginUserId, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
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
					where customer_id = '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $totalInMoney, $totalBalanceMoney, $customerId, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 明细账
			$sql = "insert into t_pre_receiving_detail(id, customer_id, in_money, balance_money, date_created,
						ref_number, ref_type, biz_user_id, input_user_id, biz_date, company_id)
					values('%s', '%s', %f, %f, now(), '', '收预收款', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $customerId, $inMoney, $totalBalanceMoney, 
					$bizUserId, $loginUserId, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$params["customerName"] = $customer["name"];
		
		// 操作成功
		return null;
	}

	/**
	 * 退还预收款
	 */
	public function returnPreReceiving(& $params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$customerId = $params["customerId"];
		$bizUserId = $params["bizUserId"];
		$bizDT = $params["bizDT"];
		$outMoney = $params["outMoney"];
		
		// 检查客户
		$customerDAO = new CustomerDAO($db);
		$customer = $customerDAO->getCustomerById($customerId);
		if (! $customer) {
			return $this->bad("客户不存在，无法预收款");
		}
		
		// 检查业务日期
		if (! $this->dateIsValid($bizDT)) {
			return $this->bad("业务日期不正确");
		}
		
		// 检查收款人是否存在
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			return $this->bad("收款人不存在");
		}
		
		$inMoney = floatval($outMoney);
		if ($outMoney <= 0) {
			return $this->bad("收款金额需要是正数");
		}
		
		$customerName = $customer["name"];
		
		$sql = "select balance_money, out_money
				from t_pre_receiving
				where customer_id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $customerId, $companyId);
		$balanceMoney = $data[0]["balance_money"];
		if (! $balanceMoney) {
			$balanceMoney = 0;
		}
		
		if ($balanceMoney < $outMoney) {
			$info = "退款金额{$outMoney}元超过余额。<br /><br />客户[{$customerName}]的预付款余额是{$balanceMoney}元";
			return $this->bad($info);
		}
		
		$totalOutMoney = $data[0]["out_money"];
		if (! $totalOutMoney) {
			$totalOutMoney = 0;
		}
		
		// 总账
		$sql = "update t_pre_receiving
				set out_money = %f, balance_money = %f
				where customer_id = '%s' and company_id = '%s' ";
		$totalOutMoney += $outMoney;
		$balanceMoney -= $outMoney;
		$rc = $db->execute($sql, $totalOutMoney, $balanceMoney, $customerId, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 明细账
		$sql = "insert into t_pre_receiving_detail(id, customer_id, out_money, balance_money,
					biz_date, date_created, ref_number, ref_type, biz_user_id, input_user_id, company_id)
				values ('%s', '%s', %f, %f, '%s', now(), '', '退预收款', '%s', '%s', '%s')";
		$rc = $db->execute($sql, $this->newId(), $customerId, $outMoney, $balanceMoney, $bizDT, 
				$bizUserId, $loginUserId, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["customerName"] = $customerName;
		
		// 操作陈功
		return null;
	}
}