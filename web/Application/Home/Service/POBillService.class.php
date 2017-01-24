<?php

namespace Home\Service;

use Home\DAO\POBillDAO;

/**
 * 采购订单Service
 *
 * @author 李静波
 */
class POBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购订单";

	/**
	 * 获得采购订单主表信息列表
	 */
	public function pobillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new POBillDAO();
		return $dao->pobillList($params);
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
		
		$db->startTrans();
		
		$dao = new POBillDAO($db);
		
		$us = new UserService();
		$bill["companyId"] = $us->getCompanyId();
		$bill["loginUserId"] = $us->getLoginUserId();
		$bill["dataOrg"] = $us->getLoginUserDataOrg();
		
		$id = $bill["id"];
		
		$log = null;
		if ($id) {
			// 编辑
			
			$rc = $dao->updatePOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑采购订单，单号：{$ref}";
		} else {
			// 新建采购订单
			
			$rc = $dao->addPOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建采购订单，单号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得采购订单的信息
	 */
	public function poBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["companyId"] = $us->getCompanyId();
		$params["loginUserId"] = $us->getLoginUserId();
		$params["loginUserName"] = $us->getLoginUserName();
		
		$dao = new POBillDAO();
		return $dao->poBillInfo($params);
	}

	/**
	 * 采购订单的商品明细
	 */
	public function poBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new POBillDAO();
		return $dao->poBillDetailList($params);
	}

	/**
	 * 审核采购订单
	 */
	public function commitPOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$dao = new POBillDAO($db);
		
		$bill = $dao->getPOBillById($id);
		
		if (! $bill) {
			$db->rollback();
			return $this->bad("要审核的采购订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["bill_status"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		$rc = $dao->commitPOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$log = "审核采购订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除采购订单
	 */
	public function deletePOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new POBillDAO($db);
		
		$rc = $dao->deletePOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$log = "删除采购订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 取消审核采购订单
	 */
	public function cancelConfirmPOBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new POBillDAO($db);
		$rc = $dao->cancelConfirmPOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		
		// 记录业务日志
		$log = "取消审核采购订单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}