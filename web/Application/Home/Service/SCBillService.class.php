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
}