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
}