<?php

namespace Home\Service;

use Home\DAO\POBillDAO;
use Home\DAO\SupplierDAO;
use Home\DAO\OrgDAO;
use Home\DAO\UserDAO;
use Home\DAO\IdGenDAO;

/**
 * 采购订单Service
 *
 * @author 李静波
 */
class POBillService extends PSIBaseService {
	private $LOG_CATEGORY = "采购订单";

	/**
	 * 生成新的采购订单号
	 */
	private function genNewBillRef() {
		$bs = new BizConfigService();
		$pre = $bs->getPOBillRefPre();
		
		$mid = date("Ymd");
		
		$sql = "select ref from t_po_bill where ref like '%s' order by ref desc limit 1";
		$data = M()->query($sql, $pre . $mid . "%");
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
		
		$id = $bill["id"];
		$dealDate = $bill["dealDate"];
		if (! $this->dateIsValid($dealDate)) {
			$db->rollback();
			return $this->bad("交货日期不正确");
		}
		
		$supplierId = $bill["supplierId"];
		$supplierDAO = new SupplierDAO($db);
		$supplier = $supplierDAO->getSupplierById($supplierId);
		if (! $supplier) {
			$db->rollback();
			return $this->bad("供应商不存在");
		}
		
		$orgId = $bill["orgId"];
		$orgDAO = new OrgDAO($db);
		$org = $orgDAO->getOrgById($orgId);
		if (! $org) {
			$db->rollback();
			return $this->bad("组织机构不存在");
		}
		
		$bizUserId = $bill["bizUserId"];
		$userDAO = new UserDAO($db);
		$user = $userDAO->getUserById($bizUserId);
		if (! $user) {
			$db->rollback();
			return $this->bad("业务员不存在");
		}
		
		$paymentType = $bill["paymentType"];
		$contact = $bill["contact"];
		$tel = $bill["tel"];
		$fax = $bill["fax"];
		$dealAddress = $bill["dealAddress"];
		$billMemo = $bill["billMemo"];
		
		$items = $bill["items"];
		
		$idGen = new IdGenDAO($db);
		
		$us = new UserService();
		$companyId = $us->getCompanyId();
		$bill["companyId"] = $companyId;
		$bill["loginUserId"] = $us->getLoginUserId();
		$bill["dataOrg"] = $us->getLoginUserDataOrg();
		
		$dao = new POBillDAO($db);
		
		$log = null;
		if ($id) {
			// 编辑
			$oldBill = $dao->getPOBillById($id);
			if (! $oldBill) {
				$db->rollback();
				return $this->bad("要编辑的采购订单不存在");
			}
			
			$ref = $oldBill["ref"];
			$dataOrg = $oldBill["dataOrg"];
			$companyId = $oldBill["companyId"];
			$billStatus = $oldBill["billStatus"];
			if ($billStatus != 0) {
				return $this->bad("当前采购订单已经审核，不能再编辑");
			}
			
			$bill["ref"] = $ref;
			$bill["dataOrg"] = $dataOrg;
			$bill["companyId"] = $companyId;
			$bill["billStatus"] = $billStatus;
			
			$rc = $dao->updatePOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑采购订单，单号：{$ref}";
		} else {
			// 新建采购订单
			
			$id = $idGen->newId();
			$bill["id"] = $id;
			
			$ref = $dao->genNewBillRef($bill);
			$bill["ref"] = $ref;
			
			$rc = $dao->addPOBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新建采购订单，单号：{$ref}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
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
		
		$sql = "select ref, bill_status from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要审核的采购订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号：$ref)已经被审核，不能再次审核");
		}
		
		$sql = "update t_po_bill
					set bill_status = 1000,
						confirm_user_id = '%s',
						confirm_date = now()
					where id = '%s' ";
		$us = new UserService();
		$rc = $db->execute($sql, $us->getLoginUserId(), $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "审核采购订单，单号：{$ref}";
		$bs = new BizlogService();
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
		
		$id = $params["id"];
		$db = M();
		
		$db->startTrans();
		
		$dao = new POBillDAO($db);
		
		$bill = $dao->getPOBillById($id);
		
		if (! $bill) {
			$db->rollback();
			return $this->bad("要删除的采购订单不存在");
		}
		$ref = $bill["ref"];
		$billStatus = $bill["billStatus"];
		if ($billStatus > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号：{$ref})已经审核，不能被删除");
		}
		
		$rc = $dao->deletePOBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
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
		
		$sql = "select ref, bill_status from t_po_bill where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要取消审核的采购订单不存在");
		}
		$ref = $data[0]["ref"];
		$billStatus = $data[0]["bill_status"];
		if ($billStatus > 1000) {
			$db->rollback();
			return $this->bad("采购订单(单号:{$ref})不能取消审核");
		}
		
		$sql = "select count(*) as cnt from t_po_pw where po_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("采购订单(单号:{$ref})已经生成了采购入库单，不能取消审核");
		}
		
		$sql = "update t_po_bill
					set bill_status = 0, confirm_user_id = null, confirm_date = null
					where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		// 记录业务日志
		$log = "取消审核采购订单，单号：{$ref}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}