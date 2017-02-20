<?php

namespace Home\Service;

use Home\DAO\ITBillDAO;

/**
 * 库间调拨Service
 *
 * @author 李静波
 */
class ITBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "库间调拨";

	/**
	 * 调拨单主表列表信息
	 */
	public function itbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new ITBillDAO($this->db());
		return $dao->itbillList($params);
	}

	/**
	 * 新建或编辑调拨单
	 */
	public function editITBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new ITBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		$bill["loginUserId"] = $this->getLoginUserId();
		
		if ($id) {
			// 编辑
			
			$rc = $dao->updateITBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑调拨单，单号：$ref";
		} else {
			// 新建调拨单
			
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			
			$rc = $dao->addITBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			$log = "新建调拨单，单号：$ref";
		}
		
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 查询某个调拨单的详情
	 */
	public function itBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new ITBillDAO($this->db());
		return $dao->itBillInfo($params);
	}

	/**
	 * 调拨单的明细记录
	 */
	public function itBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new ITBillDAO($this->db());
		return $dao->itBillDetailList($params);
	}

	/**
	 * 删除调拨单
	 */
	public function deleteITBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new ITBillDAO($db);
		
		$rc = $dao->deleteITBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		
		$bs = new BizlogService($db);
		$log = "删除调拨单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交调拨单
	 */
	public function commitITBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new ITBillDAO($db);
		$rc = $dao->commitITBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$log = "提交调拨单，单号: $ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}