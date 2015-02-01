<?php
namespace Home\Controller;
use Think\Controller;

use Home\Service\UserService;
use Home\Service\PermissionService;

use Home\Common\FIdConst;

class PermissionController extends Controller {
    public function index(){
		$us = new UserService();
		
		$this->assign("title", "首页");
		$this->assign("uri", __ROOT__ . "/");
		
		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::PERMISSION_MANAGEMENT)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
    }
	
	public function roleList() {
		if (IS_POST) {
			$ps = new PermissionService();
			
			$data = $ps->roleList();
			
			$this->ajaxReturn($data);
		}
	}
	
	public function permissionList() {
		if (IS_POST) {
			$ps = new PermissionService();
			$roleId = I("post.roleId");
			
			$data = $ps->permissionList($roleId);
			
			$this->ajaxReturn($data);
		}
	}
	
	public function userList() {
		if (IS_POST) {
			$ps = new PermissionService();
			$roleId = I("post.roleId");
			
			$data = $ps->userList($roleId);
			
			$this->ajaxReturn($data);
		}
	}
	
	public function editRole() {
		if (IS_POST) {
			$ps = new PermissionService();
			$params = array(
				"id" => I("post.id"),
				"name" => I("post.name"),
				"permissionIdList" => I("post.permissionIdList"),
				"userIdList" => I("post.userIdList")
			);
			
			$result = $ps->editRole($params);
			
			$this->ajaxReturn($result);
		}
	}
	
	public function selectPermission() {
		if (IS_POST) {
			$idList = I("post.idList");
			
			$ps = new PermissionService();
			$data = $ps->selectPermission($idList);
			
			$this->ajaxReturn($data);
		}
	}
	
	public function selectUsers() {
		if (IS_POST) {
			$idList = I("post.idList");
			
			$ps = new PermissionService();
			$data = $ps->selectUsers($idList);
			
			$this->ajaxReturn($data);
		}
	}
	
	public function deleteRole() {
		if (IS_POST) {
			$id = I("post.id");
			
			$ps = new PermissionService();
			$result = $ps->deleteRole($id);
			
			$this->ajaxReturn($result);
		}
	}
}