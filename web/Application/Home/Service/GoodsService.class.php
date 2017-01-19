<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\GoodsBomDAO;
use Home\DAO\GoodsBrandDAO;
use Home\DAO\GoodsUnitDAO;
use Home\DAO\GoodsCategoryDAO;
use Home\DAO\GoodsDAO;
use Home\DAO\GoodsSiDAO;

/**
 * 商品Service
 *
 * @author 李静波
 */
class GoodsService extends PSIBaseService {
	private $LOG_CATEGORY_GOODS = "基础数据-商品";
	private $LOG_CATEGORY_UNIT = "基础数据-商品计量单位";
	private $LOG_CATEGORY_BRAND = "基础数据-商品品牌";

	/**
	 * 返回所有商品计量单位
	 */
	public function allUnits() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new GoodsUnitDAO();
		
		return $dao->allUnits();
	}

	/**
	 * 新建或者编辑 商品计量单位
	 */
	public function editUnit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$name = $params["name"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsUnitDAO($db);
		
		$log = null;
		
		if ($id) {
			// 编辑
			
			$rc = $dao->updateUnit($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑计量单位: $name";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId($db);
			
			$params["id"] = $id;
			
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addUnit($params);
			
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增计量单位: $name";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY_UNIT);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除商品计量单位
	 */
	public function deleteUnit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsUnitDAO($db);
		
		$goodsUnit = $dao->getGoodsUnitById($id);
		if (! $goodsUnit) {
			$db->rollback();
			return $this->bad("要删除的商品计量单位不存在");
		}
		
		$name = $goodsUnit["name"];
		$params["name"] = $name;
		
		$rc = $dao->deleteUnit($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除商品计量单位: $name";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY_UNIT);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 返回所有的商品分类
	 */
	public function allCategories($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new GoodsCategoryDAO();
		return $dao->allCategories($params);
	}

	/**
	 * 获得某个商品分类的详情
	 */
	public function getCategoryInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new GoodsCategoryDAO();
		return $dao->getCategoryInfo($params);
	}

	/**
	 * 新建或者编辑商品分类
	 */
	public function editCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsCategoryDAO($db);
		
		if ($parentId) {
			// 检查id是否存在
			$parentCategory = $dao->getGoodsCategoryById($parentId);
			if (! $parentCategory) {
				$db->rollback();
				return $this->bad("上级分类不存在");
			}
		}
		
		if ($id) {
			// 编辑
			$rc = $dao->updateGoodsCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑商品分类: 编码 = {$code}， 分类名称 = {$name}";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$params["id"] = $id;
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addGoodsCategory($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增商品分类: 编码 = {$code}， 分类名称 = {$name}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除商品分类
	 */
	public function deleteCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsCategoryDAO($db);
		
		$category = $dao->getGoodsCategoryById($id);
		
		if (! $category) {
			return $this->bad("要删除的商品分类不存在");
		}
		$code = $category["code"];
		$name = $category["name"];
		$params["name"] = $name;
		
		$rc = $dao->deleteCategory($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除商品分类：  编码 = {$code}， 分类名称 = {$name}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 商品列表
	 */
	public function goodsList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new GoodsDAO();
		return $dao->goodsList($params);
	}

	/**
	 * 新建或编辑商品
	 */
	public function editGoods($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$categoryId = $params["categoryId"];
		$unitId = $params["unitId"];
		$salePrice = $params["salePrice"];
		$purchasePrice = $params["purchasePrice"];
		$barCode = $params["barCode"];
		$memo = $params["memo"];
		$brandId = $params["brandId"];
		
		$db = M();
		$db->startTrans();
		$dao = new GoodsDAO($db);
		
		$goodsUnitDAO = new GoodsUnitDAO($db);
		$unit = $goodsUnitDAO->getGoodsUnitById($unitId);
		
		if (! $unit) {
			$db->rollback();
			return $this->bad("计量单位不存在");
		}
		
		$goodsCategoryDAO = new GoodsCategoryDAO($db);
		$category = $goodsCategoryDAO->getGoodsCategoryById($categoryId);
		if (! $category) {
			$db->rollback();
			return $this->bad("商品分类不存在");
		}
		
		// 检查商品品牌
		if ($brandId) {
			$brandDAO = new GoodsBrandDAO($db);
			$brand = $brandDAO->getBrandById($brandId);
			
			if (! $brand) {
				$db->rollback();
				return $this->bad("商品品牌不存在");
			}
		}
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$specPY = $ps->toPY($spec);
		
		$params["py"] = $py;
		$params["specPY"] = $specPY;
		
		$log = null;
		
		if ($id) {
			// 编辑
			$rc = $dao->updateGoods($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
		} else {
			// 新增
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$params["id"] = $id;
			
			$us = new UserService();
			$params["dataOrg"] = $us->getLoginUserDataOrg();
			$params["companyId"] = $us->getCompanyId();
			
			$rc = $dao->addGoods($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 删除商品
	 */
	public function deleteGoods($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsDAO($db);
		$goods = $dao->getGoodsById($id);
		
		if (! $goods) {
			$db->rollback();
			return $this->bad("要删除的商品不存在");
		}
		$code = $goods["code"];
		$name = $goods["name"];
		$spec = $goods["spec"];
		
		$params["code"] = $code;
		$params["name"] = $name;
		$params["spec"] = $spec;
		
		$rc = $dao->deleteGoods($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除商品： 商品编码 = {$code}， 品名 = {$name}，规格型号 = {$spec}";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 商品字段，查询数据
	 */
	public function queryData($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		$us = new UserService();
		$params = array(
				"queryKey" => $queryKey,
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new GoodsDAO();
		return $dao->queryData($params);
	}

	/**
	 * 商品字段，查询数据
	 */
	public function queryDataWithSalePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params = array(
				"queryKey" => $queryKey,
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new GoodsDAO();
		return $dao->queryDataWithSalePrice($params);
	}

	/**
	 * 商品字段，查询数据
	 */
	public function queryDataWithPurchasePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params = array(
				"queryKey" => $queryKey,
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new GoodsDAO();
		return $dao->queryDataWithPurchasePrice($params);
	}

	/**
	 * 获得某个商品的详情
	 */
	public function getGoodsInfo($id, $categoryId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$params = array(
				"id" => $id,
				"categoryId" => $categoryId
		);
		
		$dao = new GoodsDAO();
		return $dao->getGoodsInfo($params);
	}

	/**
	 * 获得某个商品的安全库存列表
	 */
	public function goodsSafetyInventoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new GoodsSiDAO();
		return $dao->goodsSafetyInventoryList($params);
	}

	/**
	 * 获得某个商品安全库存的详情
	 */
	public function siInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$us = new UserService();
		$params["loginUserId"] = $us->getLoginUserId();
		
		$dao = new GoodsSiDAO();
		return $dao->siInfo($params);
	}

	/**
	 * 设置商品的安全
	 */
	public function editSafetyInventory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$json = $params["jsonStr"];
		$bill = json_decode(html_entity_decode($json), true);
		if ($bill == null) {
			return $this->bad("传入的参数错误，不是正确的JSON格式");
		}
		
		$db = M();
		
		$db->startTrans();
		
		$id = $bill["id"];
		
		$goodsDAO = new GoodsDAO($db);
		$goods = $goodsDAO->getGoodsById($id);
		
		if (! $goods) {
			$db->rollback();
			return $this->bad("商品不存在，无法设置商品安全库存");
		}
		$goodsCode = $goods["code"];
		$goodsName = $goods["name"];
		$goodsSpec = $goods["spec"];
		
		$dao = new GoodsSiDAO($db);
		$rc = $dao->editSafetyInventory($bill);
		if ($rc) {
			$db->rollback();
			
			return $rc;
		}
		
		$bs = new BizlogService($db);
		$log = "为商品[$goodsCode $goodsName $goodsSpec]设置安全库存";
		$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 通过条形码查询商品信息, 销售出库单使用
	 */
	public function queryGoodsInfoByBarcode($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$dao = new GoodsDAO();
		return $dao->queryGoodsInfoByBarcode($params);
	}

	/**
	 * 通过条形码查询商品信息, 采购入库单使用
	 */
	public function queryGoodsInfoByBarcodeForPW($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$dao = new GoodsDAO();
		return $dao->queryGoodsInfoByBarcodeForPW($params);
	}

	/**
	 * 查询商品种类总数
	 */
	public function getTotalGoodsCount($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$sql = "select count(*) as cnt
					from t_goods c
					where (1 = 1) ";
		$queryParam = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "c");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($code) {
			$sql .= " and (c.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (c.name like '%s' or c.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (c.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (c.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		$data = $db->query($sql, $queryParam);
		
		$result = array();
		
		$result["cnt"] = $data[0]["cnt"];
		
		return $result;
	}

	/**
	 * 获得所有的品牌
	 */
	public function allBrands() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		$us = new UserService();
		$params = array(
				"loginUserId" => $us->getLoginUserId()
		);
		
		$dao = new GoodsBrandDAO();
		return $dao->allBrands($params);
	}

	/**
	 * 新增或编辑商品品牌
	 */
	public function editBrand($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsBrandDAO($db);
		
		$log = null;
		
		$us = new UserService();
		$dataOrg = $us->getLoginUserDataOrg();
		$companyId = $us->getCompanyId();
		
		$params["dataOrg"] = $us->getLoginUserDataOrg();
		$params["companyId"] = $us->getCompanyId();
		
		if ($id) {
			// 编辑品牌
			
			$rc = $dao->updateGoodsBrand($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "编辑商品品牌[$name]";
		} else {
			// 新增品牌
			
			$idGen = new IdGenService();
			$id = $idGen->newId($db);
			$params["id"] = $id;
			
			$rc = $dao->addBrand($params);
			if ($rc) {
				$db->rollback();
				return $rc;
			}
			
			$log = "新增商品品牌[$name]";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService($db);
			$bs->insertBizlog($log, $this->LOG_CATEGORY_BRAND);
		}
		
		$db->commit();
		
		return $this->ok($id);
	}

	/**
	 * 获得某个品牌的上级品牌全称
	 */
	public function brandParentName($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new GoodsBrandDAO();
		return $dao->brandParentName($params);
	}

	/**
	 * 删除商品品牌
	 */
	public function deleteBrand($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$db->startTrans();
		
		$dao = new GoodsBrandDAO($db);
		$brand = $dao->getBrandById($id);
		
		if (! $brand) {
			$db->rollback();
			return $this->bad("要删除的品牌不存在");
		}
		$fullName = $brand["fullName"];
		$params["fullName"] = $fullName;
		
		$rc = $dao->deleteBrand($params);
		if ($rc) {
			$db->rollback();
			return $rc;
		}
		
		$log = "删除商品品牌[$fullName]";
		$bs = new BizlogService($db);
		$bs->insertBizlog($log, $this->LOG_CATEGORY_BRAND);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 商品构成
	 */
	public function goodsBOMList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$dao = new GoodsBomDAO();
		return $dao->goodsBOMList($params);
	}
}