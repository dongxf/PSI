<?php

namespace Home\DAO;

/**
 * 预付款 DAO
 *
 * @author 李静波
 */
class PrePaymentDAO extends PSIBaseExDAO {

	/**
	 * 向供应商付预付款
	 */
	public function addPrePayment(& $params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		$loginUserId = $params["loginUserId"];
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->badParam("loginUserId");
		}
		
		$supplierId = $params["supplierId"];
		$bizUserId = $params["bizUserId"];
		$bizDT = $params["bizDT"];
		$inMoney = $params["inMoney"];
		
		// 检查供应商
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			return $this->bad("供应商不存在，无法付预付款");
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
			return $this->bad("付款金额需要是正数");
		}
		
		$sql = "select in_money, balance_money from t_pre_payment
				where supplier_id = '%s' and company_id = '%s' ";
		$data = $db->query($sql, $supplierId, $companyId);
		if (! $data) {
			// 总账
			$sql = "insert into t_pre_payment(id, supplier_id, in_money, balance_money, company_id)
					values ('%s', '%s', %f, %f, '%s')";
			$rc = $db->execute($sql, $this->newId(), $supplierId, $inMoney, $inMoney, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 明细账
			$sql = "insert into t_pre_payment_detail(id, supplier_id, in_money, balance_money, date_created,
						ref_number, ref_type, biz_user_id, input_user_id, biz_date, company_id)
					values('%s', '%s', %f, %f, now(), '', '预付供应商采购货款', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $supplierId, $inMoney, $inMoney, $bizUserId, 
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
			$sql = "update t_pre_payment
					set in_money = %f, balance_money = %f
					where supplier_id = '%s' and company_id = '%s' ";
			$rc = $db->execute($sql, $totalInMoney, $totalBalanceMoney, $supplierId, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
			
			// 明细账
			$sql = "insert into t_pre_payment_detail(id, supplier_id, in_money, balance_money, date_created,
						ref_number, ref_type, biz_user_id, input_user_id, biz_date, company_id)
					values('%s', '%s', %f, %f, now(), '', '预付供应商采购货款', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $this->newId(), $supplierId, $inMoney, $totalBalanceMoney, 
					$bizUserId, $loginUserId, $bizDT, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		$params["supplierName"] = $supplier["name"];
		
		// 操作成功
		return null;
	}
}