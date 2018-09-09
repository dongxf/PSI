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

	private function subjectListInternal($parentId, $companyId) {
		$db = $this->db;
		
		$sql = "select id, code, name, category, is_leaf from t_subject
				where parent_id = '%s' and company_id = '%s'
				order by code ";
		$data = $db->query($sql, $parentId, $companyId);
		$result = [];
		foreach ( $data as $v ) {
			// 递归调用自己
			$children = $this->subjectListInternal($v["id"]);
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"category" => $v["category"],
					"is_leaf" => $v["is_leaf"],
					"children" => $children,
					"leaf" => count($children) == 0,
					"expanded" => false
			];
		}
		
		return $result;
	}

	/**
	 * 某个公司的科目码列表
	 *
	 * @param array $params        	
	 * @return array
	 */
	public function subjectList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		
		// 判断$companyId是否是公司id
		$sql = "select count(*) as cnt
				from t_org where id = '%s' and parent_id is null ";
		$data = $db->query($sql, $companyId);
		$cnt = $data[0]["cnt"];
		if ($cnt == 0) {
			return $this->emptyResult();
		}
		
		$result = [];
		
		$sql = "select id, code, name, category, is_leaf from t_subject
				where parent_id is null
				order by code ";
		$data = $db->query($sql);
		foreach ( $data as $v ) {
			$children = $this->subjectListInternal($v["id"], $companyId);
			
			$result[] = [
					"id" => $v["id"],
					"code" => $v["code"],
					"name" => $v["name"],
					"category" => $v["category"],
					"is_leaf" => $v["is_leaf"],
					"children" => $children,
					"leaf" => count($children) == 0,
					"expanded" => true
			];
		}
		
		return $result;
	}
}