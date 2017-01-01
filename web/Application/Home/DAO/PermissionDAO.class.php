<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 权限 DAO
 *
 * @author 李静波
 */
class PermissionDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	public function roleList($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
		$sql = "select r.id, r.name from t_role r ";
		$queryParams = array();
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "r", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= "	order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $queryParams);
		
		return $data;
	}
}