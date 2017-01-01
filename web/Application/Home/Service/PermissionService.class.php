<?php

namespace Home\Service;

use Home\Common\DemoConst;
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
		
		$dao = new PermissionDAO($db);
		
		$pid = explode(",", $permissionIdList);
		$doList = explode(",", $dataOrgList);
		$uid = explode(",", $userIdList);
		
		if ($id) {
			// 编辑角色
			
			$rc = $dao->modifyRole($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑角色[{$name}]";
		} else {
			// 新增角色
			
			$idGen = new IdGenService();
			$id = $idGen->newId($db);
			$us = new UserService();
			
			$params["id"] = $id;
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addRole($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增角色[{$name}]";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	public function selectPermission($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"idList" => $idList
		);
		
		$dao = new PermissionDAO();
		
		return $dao->selectPermission($params);
	}

	public function selectUsers($idList) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		
		$params = array(
				"idList" => $idList,
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new PermissionDAO();
		
		return $dao->selectUsers($params);
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