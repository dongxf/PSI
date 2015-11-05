<?php

namespace Home\Controller;

use Think\Controller;
use Home\Service\UserService;
use Home\Service\GoodsService;
use Home\Service\GoodsImportService;
use Home\Common\FIdConst;
use Home\Service\BizConfigService;
use Home\Service;

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
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
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
			$bcs = new BizConfigService();
			$this->assign("productionName", $bcs->getProductionName());
			
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

	/**
	 * 获得商品分类
	 */
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

	/**
	 * 新增或编辑商品分类
	 */
	public function editCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id"),
					"code" => I("post.code"),
					"name" => I("post.name"),
					"parentId" => I("post.parentId")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editCategory($params));
		}
	}

	/**
	 * 删除商品分类
	 */
	public function deleteCategory() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteCategory($params));
		}
	}

	/**
	 * 获得商品列表
	 */
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

	/**
	 * 新增或编辑商品
	 */
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
					"barCode" => I("post.barCode"),
					"memo" => I("post.memo")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editGoods($params));
		}
	}

	/**
	 * 删除商品
	 */
	public function deleteGoods() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->deleteGoods($params));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryData() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryData($queryKey));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryDataWithSalePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithSalePrice($queryKey));
		}
	}

	/**
	 * 商品自定义字段，查询数据
	 */
	public function queryDataWithPurchasePrice() {
		if (IS_POST) {
			$queryKey = I("post.queryKey");
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryDataWithPurchasePrice($queryKey));
		}
	}

	/**
	 * 查询某个商品的信息
	 */
	public function goodsInfo() {
		if (IS_POST) {
			$id = I("post.id");
			$gs = new GoodsService();
			$data = $gs->getGoodsInfo($id);
			$data["units"] = $gs->allUnits();
			$this->ajaxReturn($data);
		}
	}

	/**
	 * 获得商品的安全库存信息
	 */
	public function goodsSafetyInventoryList() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->goodsSafetyInventoryList($params));
		}
	}

	/**
	 * 设置安全库存时候，查询信息
	 */
	public function siInfo() {
		if (IS_POST) {
			$params = array(
					"id" => I("post.id")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->siInfo($params));
		}
	}

	/**
	 * 设置安全库存
	 */
	public function editSafetyInventory() {
		if (IS_POST) {
			$params = array(
					"jsonStr" => I("post.jsonStr")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->editSafetyInventory($params));
		}
	}

	/**
	 * 根据条形码，查询商品信息
	 */
	public function queryGoodsInfoByBarcode() {
		if (IS_POST) {
			$params = array(
					"barcode" => I("post.barcode")
			);
			$gs = new GoodsService();
			$this->ajaxReturn($gs->queryGoodsInfoByBarcode($params));
		}
	}

	public function importGoods(){
		if(IS_POST){
			$upload = new \Think\Upload();
//			$upload->maxSize = 3145728;
			$upload->exts = array('xls','xlsx');//允许上传的文件后缀
			$upload->savePath = '/Goods/';//保存路径
			//先上传文件
			$fileInfo = $upload->uploadOne($_FILES['goodsFile']);
			if( ! $fileInfo ){
				$this->error($upload->getError());
			}
			else {
				$uploadGoodsFile = './Uploads' . $fileInfo['savepath'] . $fileInfo['savename'];//获取上传到服务器文件路径
				$uploadFileExt = $fileInfo['ext'];//上传文件扩展名
				$gis = new GoodsImportService();
				$this->ajaxReturn($gis->importGoodsFromExcelFile($uploadGoodsFile,$uploadFileExt));
			}
		}
	}
}