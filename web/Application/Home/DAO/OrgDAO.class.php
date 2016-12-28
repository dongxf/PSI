<?php

namespace Home\DAO;

/**
 * 组织机构 DAO
 *
 * @author 李静波
 */
class OrgDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 做类似这种增长 '0101' => '0102'，组织机构的数据域+1
	 */
	private function incDataOrg($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 2);
		$seed = intval(substr($dataOrg, - 2)) + 1;
		
		return $pre . str_pad($seed, 2, "0", STR_PAD_LEFT);
	}

	/**
	 * 做类似这种增长 '01010001' => '01010002', 用户的数据域+1
	 */
	private function incDataOrgForUser($dataOrg) {
		$pre = substr($dataOrg, 0, strlen($dataOrg) - 4);
		$seed = intval(substr($dataOrg, - 4)) + 1;
		
		return $pre . str_pad($seed, 4, "0", STR_PAD_LEFT);
	}

	/**
	 * 新增组织机构
	 */
	public function addOrg($params) {
		$db = $this->db;
		
		// 操作成功
		return null;
	}
}