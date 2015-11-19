<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\PermissionService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;

/**
 * 权限Controller
 *
 * @author 李静波
 *        
 */
class PermissionController extends PSIBaseController {

	/**
	 * 权限管理 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::PERMISSION_MANAGEMENT)) {
			$this->initVar();
			
			$this->assign("title", "权限管理");
			
			$this->display();
		} else {
			$this->gotoLoginPage();
		}
	}

	/**
	 * 获得所有的角色列表
	 */
	public function roleList() {
		if (IS_POST) {
			$ps = new PermissionService();
			
			$data = $ps->roleList();
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 获得某个角色的所有权限
	 */
	public function permissionList() {
		if (IS_POST) {
			$ps = new PermissionService();
			$roleId = I("post.roleId");
			
			$data = $ps->permissionList($roleId);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 获得某个角色的所有用户
	 */
	public function userList() {
		if (IS_POST) {
			$ps = new PermissionService();
			$roleId = I("post.roleId");
			
			$data = $ps->userList($roleId);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 新增或编辑角色
	 */
	public function editRole() {
		if (IS_POST) {
			$ps = new PermissionService();
			$params = array(
					"id" => I("post.id"),
					"name" => I("post.name"),
					"permissionIdList" => I("post.permissionIdList"),
					"dataOrgList" => I("post.dataOrgList"),
					"userIdList" => I("post.userIdList")
			);
			
			$result = $ps->editRole($params);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 选择权限
	 */
	public function selectPermission() {
		if (IS_POST) {
			$idList = I("post.idList");
			
			$ps = new PermissionService();
			$data = $ps->selectPermission($idList);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 选择用户
	 */
	public function selectUsers() {
		if (IS_POST) {
			$idList = I("post.idList");
			
			$ps = new PermissionService();
			$data = $ps->selectUsers($idList);
			
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 删除角色
	 */
	public function deleteRole() {
		if (IS_POST) {
			$id = I("post.id");
			
			$ps = new PermissionService();
			$result = $ps->deleteRole($id);
			
			$this->ajaxReturn($result);
		}
	}

	/**
	 * 获得角色的某个权限的数据域列表
	 */
	public function dataOrgList() {
		if (IS_POST) {
			$ps = new PermissionService();
			$params = array(
					"roleId" => I("post.roleId"),
					"permissionId" => I("post.permissionId")
			);
			
			$this->ajaxReturn($ps->dataOrgList($params));
		}
	}

	/**
	 * 选择数据域
	 */
	public function selectDataOrg() {
		if (IS_POST) {
			$ps = new PermissionService();
			
			$this->ajaxReturn($ps->selectDataOrg());
		}
	}
}