<?php

namespace API\Service;

use API\DAO\CustomerApiDAO;

/**
 * 客户 API Service
 *
 * @author 李静波
 */
class CustomerApiService extends PSIApiBaseService {

	public function customerList($params) {
		$tokenId = $params["tokenId"];
		if ($this->tokenIsInvalid($tokenId)) {
			return $this->emptyResult();
		}
		
		$params["userId"] = $this->getUserIdFromTokenId($tokenId);
		
		$dao = new CustomerApiDAO($this->db());
		return $dao->customerList($params);
	}
	
	public function categoryList($params) {
		$tokenId = $params["tokenId"];
		if ($this->tokenIsInvalid($tokenId)) {
			return $this->emptyResult();
		}
		
		$params["userId"] = $this->getUserIdFromTokenId($tokenId);
		
		$dao = new CustomerApiDAO($this->db());
		return $dao->categoryList($params);
	}
}