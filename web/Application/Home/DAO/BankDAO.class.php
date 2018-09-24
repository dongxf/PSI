<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 银行账户 DAO
 *
 * @author 李静波
 */
class BankDAO extends PSIBaseExDAO {

	/**
	 * 公司列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function companyList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select g.id, g.org_code, g.name
				from t_org g
				where (g.parent_id is null) ";
		
		$ds = new DataOrgDAO($db);
		$queryParams = [];
		$rs = $ds->buildSQL(FIdConst::GL_BANK_ACCOUNT, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.org_code ";
		
		$result = [];
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"code" => $v["org_code"],
					"name" => $v["name"]
			
			];
		}
		
		return $result;
	}

	/**
	 * 某个公司的银行账户
	 *
	 * @param array $params        	
	 */
	public function bankList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		
		$loginUserId = $params["loginUserId"];
		if ($this->loginUserIdNotExists($loginUserId)) {
			return $this->emptyResult();
		}
		
		$sql = "select b.id, b.bank_name, b.bank_number, b.memo
				from t_bank_account b
				where (b.company_id = '%s') ";
		
		$ds = new DataOrgDAO($db);
		$queryParams = [];
		$queryParams[] = $companyId;
		
		$rs = $ds->buildSQL(FIdConst::GL_BANK_ACCOUNT, "b", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by b.bank_name ";
		
		$result = [];
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $v ) {
			$result[] = [
					"id" => $v["id"],
					"bankName" => $v["bank_name"],
					"bankNumber" => $v["bank_number"],
					"memo" => $v["memo"]
			];
		}
		
		return $result;
	}

	/**
	 * 新增银行账户
	 * 
	 * @param array $params        	
	 */
	public function addBank($params) {
		return $this->todo();
	}

	/**
	 * 编辑银行账户
	 * 
	 * @param array $params        	
	 */
	public function updateBank($params) {
		return $this->todo();
	}
}