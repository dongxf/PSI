<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\GoodsService;
use Home\Common\FIdConst;

class GoodsController extends Controller {

	public function index() {
		$us = new UserService();

		$this->assign("title", "商品");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::GOODS)) {
			$this->display();
		} else {
			redirect(__ROOT__ . "/Home/User/login");
		}
	}

	public function unitIndex() {
		$us = new UserService();

		$this->assign("title", "商品计量单位");
		$this->assign("uri", __ROOT__ . "/");

		$this->assign("loginUserName", $us->getLoginUserName());
		$dtFlag = getdate();
		$this->assign("dtFlag", $dtFlag[0]);

		if ($us->hasPermission(FIdConst::GOODS_UNIT)) {
			$this->display();
		} else {
			redirect("Home/User/login");
		}
	}

	public function allUnits() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->allUnits());
		}
	}

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

	public function deleteUnit() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteUnit($params));
		}
	}

	public function allCategories() {
		if (IS_POST) {
			$gs = new GoodsService();
			$this->ajaxReturn($gs->allCategories());
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
				"id" => I("post.id"),
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteCategory($params));
		}
	}

	public function goodsList() {
		if (IS_POST) {
			$params = array(
				"categoryId" => I("post.categoryId"),
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
				"salePrice" => I("post.salePrice")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoods($params));
		}
	}

	public function deleteGoods() {
		if (IS_POST) {
			$params = array(
				"id" => I("post.id"),
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
}
