<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\SRBillDAO;

/**
 * 销售退货入库单Service
 *
 * @author 李静波
 */
class SRBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "销售退货入库";

	/**
	 * 销售退货入库单主表信息列表
	 */
	public function srbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new SRBillDAO($this->db());
		return $dao->srbillList($params);
	}

	/**
	 * 销售退货入库单明细信息列表
	 */
	public function srBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SRBillDAO($this->db());
		return $dao->srBillDetailList($params);
	}

	/**
	 * 获得退货入库单单据数据
	 */
	public function srBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		
		$dao = new SRBillDAO($this->db());
		return $dao->srBillInfo($params);
	}

	/**
	 * 列出要选择的可以做退货入库的销售出库单
	 */
	public function selectWSBillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new SRBillDAO($this->db());
		return $dao->selectWSBillList($params);
	}

	/**
	 * 新增或编辑销售退货入库单
	 */
	public function editSRBill($params) {
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
		
		$dao = new SRBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		if ($id) {
			// 编辑
			
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updateSRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			$log = "编辑销售退货入库单，单号：{$ref}";
		} else {
			// 新增
			
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			$bill["companyId"] = $this->getCompanyId();
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->addSRBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建销售退货入库单，单号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得销售出库单的信息
	 */
	public function getWSBillInfoForSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SRBillDAO($this->db());
		return $dao->getWSBillInfoForSRBill($params);
	}

	/**
	 * 删除销售退货入库单
	 */
	public function deleteSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$dao = new SRBillDAO($db);
		$rc = $dao->deleteSRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$ref = $params["ref"];
		$bs = new BizlogService($db);
		$log = "删除销售退货入库单，单号：{$ref}";
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 提交销售退货入库单
	 */
	public function commitSRBill($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = $this->db();
		$db->startTrans();
		
		$params["loginUserId"] = $this->getLoginUserId();
		
		$dao = new SRBillDAO($db);
		$rc = $dao->commitSRBill($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		// 记录业务日志
		$ref = $params["ref"];
		$log = "提交销售退货入库单，单号：{$ref}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}
}