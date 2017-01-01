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

	/**
	 * 角色列表
	 */
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

	/**
	 * 某个角色的权限列表
	 */
	public function permissionList($params) {
		$db = $this->db;
		
		$roleId = $params["roleId"];
		
		$sql = "select p.id, p.name
				from t_role r, t_role_permission rp, t_permission p
				where r.id = rp.role_id and r.id = '%s' and rp.permission_id = p.id
				order by convert(p.name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $roleId);
		
		$result = array();
		foreach ( $data as $i => $v ) {
			$pid = $v["id"];
			$result[$i]["id"] = $pid;
			$result[$i]["name"] = $v["name"];
			
			$sql = "select data_org
					from t_role_permission_dataorg
					where role_id = '%s' and permission_id = '%s' ";
			$od = $db->query($sql, $roleId, $pid);
			if ($od) {
				$dataOrg = "";
				foreach ( $od as $j => $item ) {
					if ($j > 0) {
						$dataOrg .= ";";
					}
					$dataOrg .= $item["data_org"];
				}
				$result[$i]["dataOrg"] = $dataOrg;
			} else {
				$result[$i]["dataOrg"] = "*";
			}
		}
		
		return $result;
	}

	/**
	 * 某个角色包含的用户
	 */
	public function userList($params) {
		$db = $this->db;
		
		$roleId = $params["roleId"];
		
		$sql = "select u.id, u.login_name, u.name, org.full_name
				from t_role r, t_role_user ru, t_user u, t_org org
				where r.id = ru.role_id and r.id = '%s' and ru.user_id = u.id and u.org_id = org.id ";
		
		$sql .= " order by convert(org.full_name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $roleId);
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["orgFullName"] = $v["full_name"];
			$result[$i]["loginName"] = $v["login_name"];
		}
		
		return $result;
	}

	public function dataOrgList($params) {
		$db = $this->db;
		
		$roleId = $params["roleId"];
		$permissionId = $params["permissionId"];
		
		$sql = "select data_org
				from t_role_permission_dataorg
				where role_id = '%s' and permission_id = '%s' ";
		$data = $db->query($sql, $roleId, $permissionId);
		$result = array();
		if ($data) {
			foreach ( $data as $i => $v ) {
				$dataOrg = $v["data_org"];
				$result[$i]["dataOrg"] = $dataOrg;
				if ($dataOrg == "*") {
					$result[$i]["fullName"] = "[全部数据]";
				} else if ($dataOrg == "#") {
					$result[$i]["fullName"] = "[本人数据]";
				} else {
					$fullName = "";
					$sql = "select full_name from t_org where data_org = '%s'";
					$data = $db->query($sql, $dataOrg);
					if ($data) {
						$fullName = $data[0]["full_name"];
					} else {
						$sql = "select o.full_name, u.name
							from t_org o, t_user u
							where o.id = u.org_id and u.data_org = '%s' ";
						$data = $db->query($sql, $dataOrg);
						if ($data) {
							$fullName = $data[0]["full_name"] . "\\" . $data[0]["name"];
						}
					}
					
					$result[$i]["fullName"] = $fullName;
				}
			}
		} else {
			$result[0]["dataOrg"] = "*";
			$result[0]["fullName"] = "[全部数据]";
		}
		
		return $result;
	}

	public function selectDataOrg($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
		$result = array();
		$sql = "select full_name, data_org
				from t_org ";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "t_org", $loginUserId);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParams = $rs[1];
		}
		$sql .= " order by convert(full_name USING gbk) collate gbk_chinese_ci";
		
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["fullName"] = $v["full_name"];
			$result[$i]["dataOrg"] = $v["data_org"];
		}
		
		return $result;
	}
	
	/**
	 * const: 全部权限
	 */
	private $ALL_CATEGORY = "[全部]";

	/**
	 * 获得权限分类
	 */
	public function permissionCategory() {
		$db = $this->db;
		
		$result = array();
		
		$result[0]["name"] = $this->ALL_CATEGORY;
		
		$sql = "select distinct category
				from t_permission
				order by convert(category USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql);
		foreach ( $data as $i => $v ) {
			$result[$i + 1]["name"] = $v["category"];
		}
		
		return $result;
	}

	/**
	 * 按权限分类查询权限项
	 */
	public function permissionByCategory($params) {
		$db = $this->db;
		
		$category = $params["category"];
		
		$sql = "select id, name
				from t_permission ";
		
		$queryParams = array();
		if ($category != $this->ALL_CATEGORY) {
			$queryParams[] = $category;
			
			$sql .= " where category = '%s' ";
		}
		
		$sql .= " order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $queryParams);
		
		$result = array();
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["name"] = $v["name"];
		}
		
		return $result;
	}

	/**
	 * 通过id获得角色
	 */
	public function getRoleById($id) {
		$db = $this->db;
		
		$sql = "select name from t_role where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		}
		
		return array(
				"name" => $data[0]["name"]
		);
	}

	/**
	 * 删除角色
	 */
	public function deleteRole($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$sql = "delete from t_role_permission_dataorg where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_role_permission where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_role_user  where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_role where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function selectPermission($params) {
		$db = $this->db;
		
		$idList = $params["idList"];
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select id, name from t_permission
				order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql);
		
		$index = 0;
		
		foreach ( $data as $v ) {
			if (! in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];
				
				$index ++;
			}
		}
		
		return $result;
	}

	public function selectUsers($params) {
		$db = $this->db;
		
		$idList = $params["idList"];
		
		$loginUserId = $params["loginUserId"];
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select u.id, u.name, u.login_name, o.full_name
				from t_user u, t_org o
				where u.org_id = o.id ";
		$queryParams = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "u", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by convert(u.name USING gbk) collate gbk_chinese_ci";
		$data = $db->query($sql, $queryParams);
		
		$index = 0;
		
		foreach ( $data as $v ) {
			if (! in_array($v["id"], $list)) {
				$result[$index]["id"] = $v["id"];
				$result[$index]["name"] = $v["name"];
				$result[$index]["loginName"] = $v["login_name"];
				$result[$index]["orgFullName"] = $v["full_name"];
				
				$index ++;
			}
		}
		
		return $result;
	}

	public function addRole($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		$permissionIdList = $params["permissionIdList"];
		$dataOrgList = $params["dataOrgList"];
		$userIdList = $params["userIdList"];
		
		$loginUserDataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$pid = explode(",", $permissionIdList);
		$doList = explode(",", $dataOrgList);
		$uid = explode(",", $userIdList);
		
		$sql = "insert into t_role (id, name, data_org, company_id)
					values ('%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $name, $loginUserDataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		if ($pid) {
			foreach ( $pid as $i => $v ) {
				$sql = "insert into t_role_permission (role_id, permission_id)
								values ('%s', '%s')";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				// 权限的数据域
				$sql = "delete from t_role_permission_dataorg
								where role_id = '%s' and permission_id = '%s' ";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				$dataOrg = $doList[$i];
				$oList = explode(";", $dataOrg);
				foreach ( $oList as $item ) {
					if (! $item) {
						continue;
					}
					
					$sql = "insert into t_role_permission_dataorg(role_id, permission_id, data_org)
									values ('%s', '%s', '%s')";
					$rc = $db->execute($sql, $id, $v, $item);
					if ($rc === false) {
						return $this->sqlError(__METHOD__, __LINE__);
					}
				}
			}
		}
		
		if ($uid) {
			foreach ( $uid as $v ) {
				$sql = "insert into t_role_user (role_id, user_id)
								values ('%s', '%s') ";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		}
		
		// 操作成功
		return null;
	}

	public function modifyRole($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		$permissionIdList = $params["permissionIdList"];
		$dataOrgList = $params["dataOrgList"];
		$userIdList = $params["userIdList"];
		
		$pid = explode(",", $permissionIdList);
		$doList = explode(",", $dataOrgList);
		$uid = explode(",", $userIdList);
		
		$sql = "update t_role set name = '%s' where id = '%s' ";
		$rc = $db->execute($sql, $name, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_role_permission where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$sql = "delete from t_role_user where role_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		if ($pid) {
			foreach ( $pid as $i => $v ) {
				$sql = "insert into t_role_permission (role_id, permission_id)
								values ('%s', '%s')";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				// 权限的数据域
				$sql = "delete from t_role_permission_dataorg
								where role_id = '%s' and permission_id = '%s' ";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
				
				$dataOrg = $doList[$i];
				$oList = explode(";", $dataOrg);
				foreach ( $oList as $item ) {
					if (! $item) {
						continue;
					}
					
					$sql = "insert into t_role_permission_dataorg(role_id, permission_id, data_org)
									values ('%s', '%s', '%s')";
					$rc = $db->execute($sql, $id, $v, $item);
					if ($rc === false) {
						return $this->sqlError(__METHOD__, __LINE__);
					}
				}
			}
		}
		
		if ($uid) {
			foreach ( $uid as $v ) {
				$sql = "insert into t_role_user (role_id, user_id)
								values ('%s', '%s') ";
				$rc = $db->execute($sql, $id, $v);
				if ($rc === false) {
					return $this->sqlError(__METHOD__, __LINE__);
				}
			}
		}
		
		// 操作成功
		return null;
	}
}