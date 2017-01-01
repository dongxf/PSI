<?php

namespace Home\Service;

use Home\Common\DemoConst;
use Home\Common\FIdConst;
use Home\DAO\PermissionDAO;

/**
 * 权限 Service
 *
 * @author 李静波
 */
class PermissionService extends PSIBaseService {
	private $LOG_CATEGORY = "权限管理";

	public function roleList() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params = array(
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new PermissionDAO();
		
		return $dao->roleList($params);
	}

	public function permissionList($roleId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"roleId" => $roleId
		);
		
		$dao = new PermissionDAO();
		
		return $dao->permissionList($params);
	}

	public function userList($roleId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"roleId" => $roleId
		);
		
		$dao = new PermissionDAO();
		
		return $dao->userList($params);
	}

	public function editRole($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$name = $params["name"];
		$permissionIdList = $params["permissionIdList"];
		$dataOrgList = $params["dataOrgList"];
		$userIdList = $params["userIdList"];
		
		if ($this->isDemo() && $id == DemoConst::ADMIN_ROLE_ID) {
			return $this->bad("在演示环境下，系统管理角色不希望被您修改，请见谅");
		}
		
		$db = M();
		$db->startTrans();
		
		$pid = explode(",", $permissionIdList);
		$doList = explode(",", $dataOrgList);
		$uid = explode(",", $userIdList);
		
		if ($id) {
			// 编辑角色
			
			$sql = "update t_role set name = '%s' where id = '%s' ";
			$rc = $db->execute($sql, $name, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "delete from t_role_permission where role_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$sql = "delete from t_role_user where role_id = '%s' ";
			$rc = $db->execute($sql, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			if ($pid) {
				foreach ( $pid as $i => $v ) {
					$sql = "insert into t_role_permission (role_id, permission_id) 
								values ('%s', '%s')";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 权限的数据域
					$sql = "delete from t_role_permission_dataorg 
								where role_id = '%s' and permission_id = '%s' ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
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
							$db->rollback();
							return $this->sqlError(__LINE__);
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
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
			
			$log = "编辑角色[{$name}]";
		} else {
			// 新增角色
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$us = new UserService();
			$loginUserDataOrg = $us->getLoginUserDataOrg();
			$companyId = $us->getCompanyId();
			
			$sql = "insert into t_role (id, name, data_org, company_id) 
					values ('%s', '%s', '%s', '%s') ";
			$rc = $db->execute($sql, $id, $name, $loginUserDataOrg, $companyId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			if ($pid) {
				foreach ( $pid as $i => $v ) {
					$sql = "insert into t_role_permission (role_id, permission_id) 
								values ('%s', '%s')";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
					
					// 权限的数据域
					$sql = "delete from t_role_permission_dataorg 
								where role_id = '%s' and permission_id = '%s' ";
					$rc = $db->execute($sql, $id, $v);
					if ($rc === false) {
						$db->rollback();
						return $this->sqlError(__LINE__);
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
							$db->rollback();
							return $this->sqlError(__LINE__);
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
						$db->rollback();
						return $this->sqlError(__LINE__);
					}
				}
			}
			
			$log = "新增角色[{$name}]";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function selectPermission($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select id, name from t_permission 
				order by convert(name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql);
		
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

	public function selectUsers($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$list = explode(",", $idList);
		if (! $list) {
			return array();
		}
		
		$result = array();
		
		$sql = "select u.id, u.name, u.login_name, o.full_name 
				from t_user u, t_org o 
				where u.org_id = o.id ";
		$queryParams = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::PERMISSION_MANAGEMENT, "u");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = $rs[1];
		}
		
		$sql .= " order by convert(u.name USING gbk) collate gbk_chinese_ci";
		$data = M()->query($sql, $queryParams);
		
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

	/**
	 * 删除角色
	 */
	public function deleteRole($id) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		if ($this->isDemo() && $id == DemoConst::ADMIN_ROLE_ID) {
			return $this->bad("在演示环境下，系统管理角色不希望被您删除，请见谅");
		}
		
		$db = M();
		$db->startTrans();
		
		$dao = new PermissionDAO($db);
		$role = $dao->getRoleById($id);
		
		if (! $role) {
			$db->rollback();
			return $this->bad("要删除的角色不存在");
		}
		$roleName = $role["name"];
		
		$params = array(
				"id" => $id
		);
		$rc = $dao->deleteRole($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除角色[{$roleName}]";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY);
		
		$db->commit();
		
		return $this->ok();
	}

	public function dataOrgList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PermissionDAO();
		
		return $dao->dataOrgList($params);
	}

	public function selectDataOrg() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		
		$params = array(
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new PermissionDAO();
		
		return $dao->selectDataOrg($params);
	}

	/**
	 * 获得权限分类
	 */
	public function permissionCategory() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PermissionDAO();
		
		return $dao->permissionCategory();
	}

	/**
	 * 按权限分类查询权限项
	 */
	public function permissionByCategory($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new PermissionDAO();
		
		return $dao->permissionByCategory($params);
	}
}