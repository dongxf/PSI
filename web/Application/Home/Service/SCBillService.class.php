<?php

namespace Home\Service;

use Home\DAO\SCBillDAO;

/**
 * 销售合同Service
 *
 * @author 李静波
 */
class SCBillService extends PSIBaseExService {
	private $LOG_CATEGORY = "销售合同";

	/**
	 * 获得销售合同主表信息列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function scbillList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$dao = new SCBillDAO($this->db());
		return $dao->scbillList($params);
	}

	/**
	 * 销售合同详情
	 */
	public function scBillInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getLoginUserId();
		$params["loginUserName"] = $this->getLoginUserName();
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new SCBillDAO($this->db());
		return $dao->scBillInfo($params);
	}

	/**
	 * 新增或编辑销售合同
	 */
	public function editSCBill($json) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = $this->db();
		
		$db->startTrans();
		
		$dao = new SCBillDAO($db);
		
		$id = $bill["id"];
		
		$log = null;
		
		$bill["companyId"] = $this->getCompanyId();
		
		if ($id) {
			// 编辑
			
			$bill["loginUserId"] = $this->getLoginUserId();
			
			$rc = $dao->updateSCBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$ref = $bill["ref"];
			
			$log = "编辑销售合同，合同号：{$ref}";
		} else {
			// 新建销售订单
			
			$bill["loginUserId"] = $this->getLoginUserId();
			$bill["dataOrg"] = $this->getLoginUserDataOrg();
			
			$rc = $dao->addSCBill($bill);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$id = $bill["id"];
			$ref = $bill["ref"];
			
			$log = "新建销售合同，合同号：{$ref}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 销售合同商品明细
	 */
	public function scBillDetailList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params["companyId"] = $this->getCompanyId();
		
		$dao = new SCBillDAO($this->db());
		return $dao->scBillDetailList($params);
	}
}