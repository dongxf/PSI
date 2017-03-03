<?php

namespace Home\Service;

use Home\DAO\ICBillDAO;

/**
 * 库存盘点Service
 *
 * @author 李静波
 */
class ICBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "库存盘点";

	/**
	 * 获得某个盘点单的详情
	 */
	public function icBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new ICBillDAO($this->db());
		
		return $dao->icBillInfo($params);
	}

	/**
	 * 新建或编辑盘点单
	 */
	public function editICBill($params) {
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
		
		$dao = new ICBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑单据
			
			$bill["loginUserId"] = $this->getLoginUserId();
			$rc = $dao->updateICBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "编辑盘点单，单号：$ref";
		} else {
			// 新建单据
			
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->addICBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "新建盘点单，单号：$ref";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 盘点单列表
	 */
	public function icbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new ICBillDAO($this->db());
		return $dao->icbillList($params);
	}

	/**
	 * 盘点单明细记录
	 */
	public function icBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new ICBillDAO($this->db());
		return $dao->icBillDetailList($params);
	}

	/**
	 * 删除盘点单
	 */
	public function deleteICBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new ICBillDAO($db);
		$rc = $dao->deleteICBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$bs = new BizlogService($db);
		
		$ref = $params["ref"];
		$log = "删除盘点单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交盘点单
	 */
	public function commitICBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new ICBillDAO($db);
		$rc = $dao->commitICBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$bs = new BizlogService();
		$ref = $params["ref"];
		$log = "提交盘点单，单号：$ref";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		$id = $params["id"];
		return $this->ok($id);
	}
}