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
	 * 新增组织机构
	 */
	public function addOrg($params) {
		$db = $this->db;
		
		$parentId = $params["parentId"];
		$id = $params["id"];
		$name = $params["name"];
		$orgCode = $params["orgCode"];
		
		$sql = "select full_name from t_org where id = '%s' ";
		$parentOrg = $db->query($sql, $parentId);
		$fullName = "";
		if (! $parentOrg) {
			$parentId = null;
			$fullName = $name;
		} else {
			$fullName = $parentOrg[0]["full_name"] . "\\" . $name;
		}
		
		if ($parentId == null) {
			$dataOrg = "01";
			$sql = "select data_org from t_org
						where parent_id is null
						order by data_org desc limit 1";
			$data = $db->query($sql);
			if ($data) {
				$dataOrg = $this->incDataOrg($data[0]["data_org"]);
			}
			
			$sql = "insert into t_org (id, name, full_name, org_code, parent_id, data_org)
						values ('%s', '%s', '%s', '%s', null, '%s')";
			
			$rc = $db->execute($sql, $id, $name, $fullName, $orgCode, $dataOrg);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$dataOrg = "";
			$sql = "select data_org from t_org
						where parent_id = '%s'
						order by data_org desc limit 1";
			$data = $db->query($sql, $parentId);
			if ($data) {
				$dataOrg = $this->incDataOrg($data[0]["data_org"]);
			} else {
				$sql = "select data_org from t_org where id = '%s' ";
				$data = $db->query($sql, $parentId);
				if (! $data) {
					return $this->bad("上级组织机构不存在");
				}
				$dataOrg = $data[0]["data_org"] . "01";
			}
			
			$sql = "insert into t_org (id, name, full_name, org_code, parent_id, data_org)
						values ('%s', '%s', '%s', '%s', '%s', '%s')";
			
			$rc = $db->execute($sql, $id, $name, $fullName, $orgCode, $parentId, $dataOrg);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}
}