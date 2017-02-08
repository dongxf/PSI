<?php

namespace Home\Service;

use Home\DAO\PRBillDAO;

/**
 * 采购退货出库单Service
 *
 * @author 李静波
 */
class PRBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "采购退货出库";

	public function prBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new PRBillDAO();
		return $dao->prBillInfo($params);
	}

	/**
	 * 新建或编辑采购退货出库单
	 */
	public function editPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		$db->startTrans();
		
		$dao = new PRBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑采购退货出库单
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updatePRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "编辑采购退货出库单，单号：$ref";
		} else {
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->addPRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建采购退货出库单，单号：$ref";
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 选择可以退货的采购入库单
	 */
	public function selectPWBillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new PRBillDAO();
		return $dao->selectPWBillList($params);
	}

	/**
	 * 查询采购入库单的详细信息
	 */
	public function getPWBillInfoForPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PRBillDAO();
		return $dao->getPWBillInfoForPRBill($params);
	}

	/**
	 * 采购退货出库单列表
	 */
	public function prbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new PRBillDAO();
		return $dao->prbillList($params);
	}

	/**
	 * 采购退货出库单明细列表
	 */
	public function prBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PRBillDAO();
		return $dao->prBillDetailList($params);
	}

	/**
	 * 删除采购退货出库单
	 */
	public function deletePRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new PRBillDAO($db);
		$rc = $dao->deletePRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$bs = new BizlogService();
		$log = "删除采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交采购退货出库单
	 */
	public function commitPRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		
		$db->startTrans();
		
		$dao = new PRBillDAO($db);
		
		$rc = $dao->commitPRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$bs = new BizlogService($db);
		$log = "提交采购退货出库单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}