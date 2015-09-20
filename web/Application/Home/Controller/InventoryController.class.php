<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\InventoryService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;

/**
 * 库存Controller
 *
 * @author 李静波
 *        
 */
class InventoryController extends Controller {

	/**
	 * 库存建账 - 主页面
	 */
	public function initIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::INVENTORY_INIT)) {
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
			$this->assign("title", "库存建账");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 库存账查询
	 */
	public function inventoryQuery() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::INVENTORY_QUERY)) {
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
			$this->assign("title", "库存账查询");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	/**
	 * 获得所有仓库列表
	 */
	public function warehouseList() {
		if (IS_POST) {
			$is = new InventoryService();
			$this->ajaxReturn($is->warehouseList());
		}
	}

	/**
	 * 库存总账
	 */
	public function inventoryList() {
		if (IS_POST) {
			$params = array(
					"warehouseId" => I("post.warehouseId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$is = new InventoryService();
			$this->ajaxReturn($is->inventoryList($params));
		}
	}

	/**
	 * 库存明细账
	 */
	public function inventoryDetailList() {
		if (IS_POST) {
			$params = array(
					"warehouseId" => I("post.warehouseId"),
					"goodsId" => I("post.goodsId"),
					"dtFrom" => I("post.dtFrom"),
					"dtTo" => I("post.dtTo"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$is = new InventoryService();
			$this->ajaxReturn($is->inventoryDetailList($params));
		}
	}
}
