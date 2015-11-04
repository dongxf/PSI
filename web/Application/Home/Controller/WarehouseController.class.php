<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\WarehouseService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;

/**
 * 仓库Controller
 *
 * @author 李静波
 *        
 */
class WarehouseController extends Controller {

	/**
	 * 仓库 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::WAREHOUSE)) {
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
			$this->assign("title", "仓库");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			//$ts = new BizConfigService();
			$this->assign("warehouseUsesOrg", false);
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 仓库列表
	 */
	public function warehouseList() {
		if (IS_POST) {
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->warehouseList());
		}
	}

	/**
	 * 新增或编辑仓库
	 */
	public function editWarehouse() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->editWarehouse($params));
		}
	}

	/**
	 * 删除仓库
	 */
	public function deleteWarehouse() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->deleteWarehouse($params));
		}
	}

	/**
	 * 仓库自定义字段，查询数据
	 */
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$fid = I("post.fid");
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->queryData($queryKey, $fid));
		}
	}

	/**
	 * 使用仓库的组织机构列表
	 */
	public function warehouseOrgList() {
		if (IS_POST) {
			$params = array(
					"warehouseId" => I("post.warehouseId"),
					"fid" => I("post.fid")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->warehouseOrgList($params));
		}
	}

	/**
	 * 查询组织机构树
	 */
	public function allOrgs() {
		$ws = new WarehouseService();
		
		$this->ajaxReturn($ws->allOrgs());
	}

	/**
	 * 为仓库增加组织机构
	 */
	public function addOrg() {
		if (IS_POST) {
			$params = array(
					"warehouseId" => I("post.warehouseId"),
					"fid" => I("post.fid"),
					"orgId" => I("post.orgId")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->addOrg($params));
		}
	}

	/**
	 * 为仓库移走组织机构
	 */
	public function deleteOrg() {
		if (IS_POST) {
			$params = array(
					"warehouseId" => I("post.warehouseId"),
					"fid" => I("post.fid"),
					"orgId" => I("post.orgId")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->deleteOrg($params));
		}
	}

	/**
	 * 从组织机构的视角查看仓库信息
	 */
	public function orgViewWarehouseList() {
		if (IS_POST) {
			$params = array(
					"orgId" => I("post.orgId")
			);
			$ws = new WarehouseService();
			$this->ajaxReturn($ws->orgViewWarehouseList($params));
		}
	}
}