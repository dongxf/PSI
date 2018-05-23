<?php

namespace H5\Service;

use Home\Service\CustomerService;
use H5\DAO\CustomerDAOH5;

/**
 * 客户Service for H5
 *
 * @author 李静波
 */
class CustomerServiceH5 extends CustomerService {

	public function queryCustomerCategoryH5($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new CustomerDAOH5($this->db());
		return $dao->queryCustomerCategoryH5($params);
	}
}