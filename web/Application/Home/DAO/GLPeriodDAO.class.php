<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 会计期间 DAO
 *
 * @author 李静波
 */
class GLPeriodDAO extends PSIBaseExDAO {

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
		$rs = $ds->buildSQL(FIdConst::GL_PERIOD, "g", $loginUserId);
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
	 * 某个公司的全部会计期间
	 */
	public function periodList($params) {
		$db = $this->db;
		
		$companyId = $params["companyId"];
		
		$sql = "select acc_year, acc_month, acc_gl_kept, acc_gl_closed,
					acc_detail_kept, acc_detail_closed, period_closed, year_forward
				from t_acc_period
				where company_id = '%s' 
				order by acc_year desc, acc_month asc";
		$data = $db->query($sql, $companyId);
		
		$result = [];
		$mark = "√";
		foreach ( $data as $v ) {
			$result[] = [
					"year" => $v["acc_year"],
					"month" => $v["acc_month"],
					"glKept" => $v["acc_gl_kept"] == 1 ? $mark : null,
					"glClosed" => $v["acc_gl_closed"] == 1 ? $mark : null,
					"detailKept" => $v["acc_detail_kept"] == 1 ? $mark : null,
					"detailClosed" => $v["acc_detail_closed"] == 1 ? $mark : null,
					"periodClosed" => $v["period_closed"] == 1 ? $mark : null,
					"yearForward" => $v["year_forward"] == 1 ? $mark : null
			];
		}
		
		return $result;
	}
}