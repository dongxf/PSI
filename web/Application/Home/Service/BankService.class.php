<?php

namespace Home\Service;

use Home\DAO\BankDAO;

/**
 * 银行账户Service
 *
 * @author 李静波
 */
class BankService extends PSIBaseExService {
	private $LOG_CATEGORY = "银行账户";

	/**
	 * 返回所有的公司列表
	 *
	 * @return array
	 */
	public function companyList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = [
				"loginUserId" => $this->getLoginUserId()
		];
		
		$dao = new BankDAO($this->db());
		return $dao->companyList($params);
	}

	/**
	 * 某个公司的银行账户
	 *
	 * @param array $params        	
	 */
	public function bankList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = [
				"loginUserId" => $this->getLoginUserId()
		];
		
		$dao = new BankDAO($this->db());
		return $dao->bankList($params);
	}

	/**
	 * 新增或编辑银行账户
	 *
	 * @param array $params        	
	 */
	public function editBank($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$bankName = $params["bankName"];
		$bankNumber = $params["bankNumber"];
		
		$params["dataOrg"] = $this->getLoginUserDataOrg();
		
		$db = $this->db();
		$db->startTrans();
		
		$log = null;
		$dao = new BankDAO($db);
		if ($id) {
			// 编辑
			$rc = $dao->updateBank($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑银行账户：{$bankName}-{$bankNumber}";
		} else {
			// 新增
			$rc = $dao->addBank($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增银行账户：{$bankName}-{$bankNumber}";
		}
		
		// 记录业务日志
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		return $this->ok();
	}
}