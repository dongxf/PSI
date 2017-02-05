<?php

namespace Home\Service;

use Home\DAO\PWBillDAO;

/**
 * 采购入库Service
 *
 * @author 李静波
 */
class PWBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购入库";

	/**
	 * 获得采购入库单主表列表
	 */
	public function pwbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new PWBillDAO();
		return $dao->pwbillList($params);
	}

	/**
	 * 获得采购入库单商品明细记录列表
	 */
	public function pwBillDetailList($pwbillId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"id" => $pwbillId
		);
		
		$dao = new PWBillDAO();
		return $dao->pwBillDetailList($params);
	}

	/**
	 * 新建或编辑采购入库单
	 */
	public function editPWBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$id = $bill["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new PWBillDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑采购入库单
			
			$rc = $dao->updatePWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑采购入库单: 单号 = {$ref}";
		} else {
			// 新建采购入库单
			
			$us = new UserService();
			$bill["companyId"] = $us->getCompanyId();
			$bill["loginUserId"] = $us->getLoginUserId();
			$bill["dataOrg"] = $us->getLoginUserDataOrg();
			
			$rc = $dao->addPWBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$pobillRef = $bill["pobillRef"];
			if ($pobillRef) {
				// 从采购订单生成采购入库单
				$log = "从采购订单(单号：{$pobillRef})生成采购入库单: 单号 = {$ref}";
			} else {
				// 手工新建采购入库单
				$log = "新建采购入库单: 单号 = {$ref}";
			}
		}
		
		// 同步库存账中的在途库存
		$rc = $dao->updateAfloatInventoryByPWBill($bill);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个采购入库单的信息
	 */
	public function pwBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		$params["loginUserName"] = $us->getLoginUserName();
		$params["companyId"] = $us->getCompanyId();
		
		$dao = new PWBillDAO();
		return $dao->pwBillInfo($params);
	}

	/**
	 * 删除采购入库单
	 */
	public function deletePWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$db->startTrans();
		
		$dao = new PWBillDAO($db);
		$params = array(
				"id" => $id
		);
		$rc = $dao->deletePWBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$log = "删除采购入库单: 单号 = {$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购入库单
	 */
	public function commitPWBill($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = M();
		$db->startTrans();
		
		$us = new UserService();
		$params = array(
				"id" => $id,
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new PWBillDAO($db);
		
		$rc = $dao->commitPWBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		
		// 业务日志
		$log = "提交采购入库单: 单号 = {$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}