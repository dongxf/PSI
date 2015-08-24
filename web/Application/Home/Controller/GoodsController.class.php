<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\GoodsService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;
use Home\Service\Home\Service;

/**
 * 商品Controller
 *
 * @author 李静波
 *        
 */
class GoodsController extends Controller {

	/**
	 * 商品主页面
	 */
	public function index() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GOODS)) {
			$this->assign("title", "商品");
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
	 * 商品计量单位主页面
	 */
	public function unitIndex() {
		$us = new UserService();
		
		if ($us->hasPermission(FIdConst::GOODS_UNIT)) {
			$this->assign("title", "商品计量单位");
			$this->assign("uri", __ROOT__ . "/");
			
			$this->assign("loginUserName", $us->getLoignUserNameWithOrgFullName());
			$dtFlag = getdate();
			$this->assign("dtFlag", $dtFlag[0]);
			
			$this->display();
		} else {
			redirect("Home/User/login");
		}
	}

	/**
	 * 获得所有的商品计量单位列表
	 */
	public function allUnits() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->allUnits());
		}
	}

	/**
	 * 新增或编辑商品单位
	 */
	public function editUnit() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"name" => I("post.name")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editUnit($params));
		}
	}

	/**
	 * 删除商品计量单位
	 */
	public function deleteUnit() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteUnit($params));
		}
	}

	public function allCategories() {
		if (IS_POST) {
			$gs = new GoodsService();
			$params = array(
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"barCode" => I("post.barCode")
			);
			$this->ajaxReturn($gs->allCategories($params));
		}
	}

	public function editCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editCategory($params));
		}
	}

	public function deleteCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteCategory($params));
		}
	}

	public function goodsList() {
		if (IS_POST) {
			$params = array(
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"barCode" => I("post.barCode"),
					"page" => I("post.page"),
					"start" => I("post.start"),
					"limit" => I("post.limit")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsList($params));
		}
	}

	public function editGoods() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"categoryId" => I("post.categoryId"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"spec" => I("post.spec"),
					"unitId" => I("post.unitId"),
					"salePrice" => I("post.salePrice"),
					"purchasePrice" => I("post.purchasePrice"),
					"barCode" => I("post.barCode")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoods($params));
		}
	}

	public function deleteGoods() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteGoods($params));
		}
	}

	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryData($queryKey));
		}
	}

	public function queryDataWithSalePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithSalePrice($queryKey));
		}
	}

	public function queryDataWithPurchasePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithPurchasePrice($queryKey));
		}
	}
	
	public function goodsInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$gs = new GoodsService();
			$data = $gs->getGoodsInfo($id);
			$data["units"] = $gs->allUnits();
			$this->ajaxReturn($data);
		}
	}

	public function goodsSafetyInventoryList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsSafetyInventoryList($params));
		}
	}

	public function siInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->siInfo($params));
		}
	}

	public function editSafetyInventory() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editSafetyInventory($params));
		}
	}

	public function queryGoodsInfoByBarcode() {
		if (IS_POST) {
			$params = array(
					"barcode" => I("post.barcode")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryGoodsInfoByBarcode($params));
		}
	}
}