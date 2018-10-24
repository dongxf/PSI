<?php

namespace Home\Service;

use Home\DAO\GLPeriodDAO;

/**
 * 会计期间 Service
 *
 * @author 李静波
 */
class GLPeriodService extends PSIBaseExService {
	private $LOG_CATEGORY = "会计期间";

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
		
		$dao = new GLPeriodDAO($this->db());
		return $dao->companyList($params);
	}
}