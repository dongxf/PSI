<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 会计科目 DAO
 *
 * @author 李静波
 */
class SubjectDAO extends PSIBaseExDAO {

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
		$rs = $ds->buildSQL(FIdConst::GL_SUBJECT, "g", $loginUserId);
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
}