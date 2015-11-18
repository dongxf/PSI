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
class WarehouseController extends PSIBaseController {

	/**
	 * 仓库 - 主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::WAREHOUSE)) {
			$this->initVar();
			
			$this->assign("title", "仓库");
			
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
}