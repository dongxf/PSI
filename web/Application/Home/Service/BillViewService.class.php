<?php

namespace Home\Service;

use Home\DAO\PWBillDAO;
use Home\DAO\PRBillDAO;
use Home\DAO\WSBillDAO;
use Home\DAO\SRBillDAO;
use Home\DAO\ITBillDAO;
use Home\DAO\ICBillDAO;

/**
 * 查看单据Service
 *
 * @author 李静波
 */
class BillViewService extends PSIBaseExService {

	/**
	 * 由单号查询采购入库单信息
	 *
	 * @param string $ref
	 *        	采购入库单单号
	 * @return array|NULL
	 */
	public function pwBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PWBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}

	/**
	 * 由单号查询销售出库单信息
	 *
	 * @param string $ref        	
	 * @return array|NULL
	 */
	public function wsBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new WSBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}

	/**
	 * 由单号查询采购退货出库单
	 *
	 * @param string $ref        	
	 * @return array|NULL
	 */
	public function prBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PRBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}

	/**
	 * 由单号查询销售退货入库单信息
	 *
	 * @param string $ref        	
	 * @return array|NULL
	 */
	public function srBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new SRBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}

	/**
	 * 由单号查询调拨单信息
	 *
	 * @param string $ref
	 *        	单号
	 * @return array|NULL
	 */
	public function itBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new ITBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}

	/**
	 * 由单号查询盘点单信息
	 *
	 * @param string $ref        	
	 * @return array|NULL
	 */
	public function icBillInfo($ref) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new ICBillDAO($this->db());
		return $dao->getFullBillDataByRef($ref);
	}
}