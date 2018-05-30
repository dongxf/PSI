<?php

namespace API\Service;

use API\DAO\SOBillApiDAO;

/**
 * 销售订单 API Service
 *
 * @author 李静波
 */
class SOBillApiService extends PSIApiBaseService {

	public function sobillList($params) {
		$tokenId = $params["tokenId"];
		if ($this->tokenIsInvalid($tokenId)) {
			return $this->emptyResult();
		}
		
		$params["loginUserId"] = $this->getUserIdFromTokenId($tokenId);
		
		$dao = new SOBillApiDAO($this->db());
		return $dao->sobillList($params);
	}
}