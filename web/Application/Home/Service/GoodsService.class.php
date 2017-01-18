<?php

namespace Home\Service;

use Home\Common\FIdConst;
use Home\DAO\GoodsBomDAO;
use Home\DAO\GoodsBrandDAO;
use Home\DAO\GoodsUnitDAO;
use Home\DAO\GoodsCategoryDAO;

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
		
		$categoryId = $params["categoryId"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
		
		$db = M();
		$result = array();
		$sql = "select g.id, g.code, g.name, g.sale_price, g.spec,  g.unit_id, u.name as unit_name,
					g.purchase_price, g.bar_code, g.memo, g.data_org, g.brand_id
				from t_goods g, t_goods_unit u 
				where (g.unit_id = u.id) and (g.category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "g");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		if ($code) {
			$sql .= " and (g.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (g.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		
		$sql .= " order by g.code limit %d, %d";
		$queryParam[] = $start;
		$queryParam[] = $limit;
		$data = $db->query($sql, $queryParam);
		
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["salePrice"] = $v["sale_price"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitId"] = $v["unit_id"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["purchasePrice"] = $v["purchase_price"] == 0 ? null : $v["purchase_price"];
			$result[$i]["barCode"] = $v["bar_code"];
			$result[$i]["memo"] = $v["memo"];
			$result[$i]["dataOrg"] = $v["data_org"];
			
			$brandId = $v["brand_id"];
			if ($brandId) {
				$result[$i]["brandFullName"] = $this->getBrandFullNameById($db, $brandId);
			}
		}
		
		$sql = "select count(*) as cnt from t_goods g where (g.category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "g");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		if ($code) {
			$sql .= " and (g.code like '%s') ";
			$queryParam[] = "%{$code}%";
		}
		if ($name) {
			$sql .= " and (g.name like '%s' or g.py like '%s') ";
			$queryParam[] = "%{$name}%";
			$queryParam[] = "%{$name}%";
		}
		if ($spec) {
			$sql .= " and (g.spec like '%s')";
			$queryParam[] = "%{$spec}%";
		}
		if ($barCode) {
			$sql .= " and (g.bar_code = '%s') ";
			$queryParam[] = $barCode;
		}
		
		$data = $db->query($sql, $queryParam);
		$totalCount = $data[0]["cnt"];
		
		return array(
				"goodsList" => $result,
				"totalCount" => $totalCount
		);
	}

	public function getBrandFullNameById($db, $brandId) {
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $brandId);
		if ($data) {
			return $data[0]["full_name"];
		} else {
			return null;
		}
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
		
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $unitId);
		if (! $data) {
			$db->rollback();
			return $this->bad("计量单位不存在");
		}
		$sql = "select name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		if (! $data) {
			$db->rollback();
			return $this->bad("商品分类不存在");
		}
		
		// 检查商品品牌
		if ($brandId) {
			$sql = "select name from t_goods_brand where id = '%s' ";
			$data = $db->query($sql, $brandId);
			if (! $data) {
				$db->rollback();
				return $this->bad("商品品牌不存在");
			}
		}
		
		$ps = new PinyinService();
		$py = $ps->toPY($name);
		$specPY = $ps->toPY($spec);
		$log = null;
		
		if ($id) {
			// 编辑
			// 检查商品编码是否唯一
			$sql = "select count(*) as cnt from t_goods where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [{$code}]的商品已经存在");
			}
			
			// 如果录入了条形码，则需要检查条形码是否唯一
			if ($barCode) {
				$sql = "select count(*) as cnt from t_goods where bar_code = '%s' and id <> '%s' ";
				$data = $db->query($sql, $barCode, $id);
				$cnt = $data[0]["cnt"];
				if ($cnt != 0) {
					$db->rollback();
					return $this->bad("条形码[{$barCode}]已经被其他商品使用");
				}
			}
			
			$sql = "update t_goods
					set code = '%s', name = '%s', spec = '%s', category_id = '%s', 
					    unit_id = '%s', sale_price = %f, py = '%s', purchase_price = %f,
						bar_code = '%s', memo = '%s', spec_py = '%s',
						brand_id = if('%s' = '', null, '%s')
					where id = '%s' ";
			
			$rc = $db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
					$purchasePrice, $barCode, $memo, $specPY, $brandId, $brandId, $id);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "编辑商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
		} else {
			// 新增
			// 检查商品编码是否唯一
			$sql = "select count(*) as cnt from t_goods where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				$db->rollback();
				return $this->bad("编码为 [{$code}]的商品已经存在");
			}
			
			// 如果录入了条形码，则需要检查条形码是否唯一
			if ($barCode) {
				$sql = "select count(*) as cnt from t_goods where bar_code = '%s' ";
				$data = $db->query($sql, $barCode);
				$cnt = $data[0]["cnt"];
				if ($cnt != 0) {
					$db->rollback();
					return $this->bad("条形码[{$barCode}]已经被其他商品使用");
				}
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			$companyId = $us->getCompanyId();
			
			$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price, 
						py, purchase_price, bar_code, memo, data_org, company_id, spec_py, brand_id)
					values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', %f, '%s', '%s', '%s', '%s', '%s',
						if('%s' = '', null, '%s'))";
			$rc = $db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, 
					$py, $purchasePrice, $barCode, $memo, $dataOrg, $companyId, $specPY, $brandId, 
					$brandId);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
			
			$log = "新增商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
		}
		
		// 记录业务日志
		if ($log) {
			$bs = new BizlogService();
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
		
		$sql = "select code, name, spec from t_goods where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的商品不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];
		$spec = $data[0]["spec"];
		
		// 判断商品是否能删除
		$sql = "select count(*) as cnt from t_po_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("商品[{$code} {$name}]已经在采购订单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_pw_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("商品[{$code} {$name}]已经在采购入库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_ws_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("商品[{$code} {$name}]已经在销售出库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_inventory_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("商品[{$code} {$name}]在业务中已经使用了，不能删除");
		}
		
		$sql = "delete from t_goods where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除商品： 商品编码 = {$code}， 品名 = {$name}，规格型号 = {$spec}";
		$bs = new BizlogService();
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
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') ";
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1001-01", "g");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code 
				limit 20";
		$data = M()->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
		}
		
		return $result;
	}

	/**
	 * 商品字段，查询数据
	 *
	 * @param unknown $queryKey        	
	 */
	public function queryDataWithSalePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name, g.sale_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s'
					or g.spec like '%s' or g.spec_py like '%s') ";
		
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1001-01", "g");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code 
				limit 20";
		$data = M()->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["salePrice"] = $v["sale_price"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		return $result;
	}

	/**
	 * 商品字段，查询数据
	 */
	public function queryDataWithPurchasePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$key = "%{$queryKey}%";
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name, g.purchase_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s' 
					or g.spec like '%s' or g.spec_py like '%s') ";
		
		$queryParams = array();
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		$queryParams[] = $key;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL("1001-01", "g");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code 
				limit 20";
		$data = M()->query($sql, $queryParams);
		$result = array();
		foreach ( $data as $i => $v ) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["purchasePrice"] = $v["purchase_price"] == 0 ? null : $v["purchase_price"];
			$result[$i]["memo"] = $v["memo"];
		}
		
		return $result;
	}

	/**
	 * 获得某个商品的详情
	 */
	public function getGoodsInfo($id, $categoryId) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$db = M();
		
		$sql = "select category_id, code, name, spec, unit_id, sale_price, purchase_price, 
					bar_code, memo, brand_id
				from t_goods
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			$result = array();
			$categoryId = $data[0]["category_id"];
			$result["categoryId"] = $categoryId;
			
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["unitId"] = $data[0]["unit_id"];
			$result["salePrice"] = $data[0]["sale_price"];
			$brandId = $data[0]["brand_id"];
			$result["brandId"] = $brandId;
			
			$v = $data[0]["purchase_price"];
			if ($v == 0) {
				$result["purchasePrice"] = null;
			} else {
				$result["purchasePrice"] = $v;
			}
			
			$result["barCode"] = $data[0]["bar_code"];
			$result["memo"] = $data[0]["memo"];
			
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $categoryId);
			if ($data) {
				$result["categoryName"] = $data[0]["full_name"];
			}
			
			if ($brandId) {
				$sql = "select full_name from t_goods_brand where id = '%s' ";
				$data = $db->query($sql, $brandId);
				$result["brandFullName"] = $data[0]["full_name"];
			}
			
			return $result;
		} else {
			$result = array();
			
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $categoryId);
			if ($data) {
				$result["categoryId"] = $categoryId;
				$result["categoryName"] = $data[0]["full_name"];
			}
			return $result;
		}
	}

	/**
	 * 获得某个商品的安全库存列表
	 */
	public function goodsSafetyInventoryList($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select u.name
				from t_goods g, t_goods_unit u
				where g.id = '%s' and g.unit_id = u.id";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		$goodsUnitName = $data[0]["name"];
		
		$sql = "select w.id as warehouse_id, w.code as warehouse_code, w.name as warehouse_name,
					s.safety_inventory, s.inventory_upper
				from t_warehouse w
				left join t_goods_si s
				on w.id = s.warehouse_id and s.goods_id = '%s'
				where w.inited = 1 ";
		$queryParams = array();
		$queryParams[] = $id;
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "w");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		$sql .= " order by w.code";
		$data = $db->query($sql, $queryParams);
		$r = array();
		foreach ( $data as $i => $v ) {
			$r[$i]["warehouseId"] = $v["warehouse_id"];
			$r[$i]["warehouseCode"] = $v["warehouse_code"];
			$r[$i]["warehouseName"] = $v["warehouse_name"];
			$r[$i]["safetyInventory"] = $v["safety_inventory"];
			$r[$i]["inventoryUpper"] = $v["inventory_upper"];
			$r[$i]["unitName"] = $goodsUnitName;
		}
		
		foreach ( $r as $i => $v ) {
			$sql = "select balance_count
					from t_inventory
					where warehouse_id = '%s' and goods_id = '%s' ";
			$data = $db->query($sql, $v["warehouseId"], $id);
			if (! $data) {
				$result[$i]["inventoryCount"] = 0;
			} else {
				$result[$i]["inventoryCount"] = $data[0]["balance_count"];
			}
			
			$result[$i]["warehouseCode"] = $v["warehouseCode"];
			$result[$i]["warehouseName"] = $v["warehouseName"];
			$result[$i]["safetyInventory"] = $v["safetyInventory"];
			$result[$i]["inventoryUpper"] = $v["inventoryUpper"];
			$result[$i]["unitName"] = $goodsUnitName;
		}
		
		return $result;
	}

	/**
	 * 获得某个商品安全库存的详情
	 */
	public function siInfo($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$id = $params["id"];
		
		$result = array();
		
		$db = M();
		$sql = "select u.name
				from t_goods g, t_goods_unit u
				where g.id = '%s' and g.unit_id = u.id";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		$goodsUnitName = $data[0]["name"];
		
		$sql = "select w.id as warehouse_id, w.code as warehouse_code, 
					w.name as warehouse_name,
					s.safety_inventory, s.inventory_upper
				from t_warehouse w
				left join t_goods_si s
				on w.id = s.warehouse_id and s.goods_id = '%s'
				where w.inited = 1 ";
		$queryParams = array();
		$queryParams[] = $id;
		
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "w");
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by w.code ";
		$data = $db->query($sql, $queryParams);
		foreach ( $data as $i => $v ) {
			$result[$i]["warehouseId"] = $v["warehouse_id"];
			$result[$i]["warehouseCode"] = $v["warehouse_code"];
			$result[$i]["warehouseName"] = $v["warehouse_name"];
			$result[$i]["safetyInventory"] = $v["safety_inventory"] ? $v["safety_inventory"] : 0;
			$result[$i]["inventoryUpper"] = $v["inventory_upper"] ? $v["inventory_upper"] : 0;
			$result[$i]["unitName"] = $goodsUnitName;
		}
		
		return $result;
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
		
		$id = $bill["id"];
		$items = $bill["items"];
		
		$idGen = new IdGenService();
		
		$db->startTrans();
		
		$sql = "select code, name, spec from t_goods where id = '%s'";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("商品不存在，无法设置商品安全库存");
		}
		$goodsCode = $data[0]["code"];
		$goodsName = $data[0]["name"];
		$goodsSpec = $data[0]["spec"];
		
		$sql = "delete from t_goods_si where goods_id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		foreach ( $items as $v ) {
			$warehouseId = $v["warehouseId"];
			$si = $v["si"];
			if (! $si) {
				$si = 0;
			}
			if ($si < 0) {
				$si = 0;
			}
			$upper = $v["invUpper"];
			if (! $upper) {
				$upper = 0;
			}
			if ($upper < 0) {
				$upper = 0;
			}
			$sql = "insert into t_goods_si(id, goods_id, warehouse_id, safety_inventory, inventory_upper)
						values ('%s', '%s', '%s', %d, %d)";
			$rc = $db->execute($sql, $idGen->newId(), $id, $warehouseId, $si, $upper);
			if ($rc === false) {
				$db->rollback();
				return $this->sqlError(__LINE__);
			}
		}
		
		$bs = new BizlogService();
		$log = "为商品[$goodsCode $goodsName $goodsSpec]设置安全库存";
		$bs->insertBizlog($log, $this->LOG_CATEGORY_GOODS);
		
		$db->commit();
		
		return $this->ok();
	}

	/**
	 * 通过条形码查询商品信息, 销售出库单使用
	 */
	public function queryGoodsInfoByBarcode($params) {
		$barcode = $params["barcode"];
		
		$result = array();
		
		$db = M();
		$sql = "select g.id, g.code, g.name, g.spec, g.sale_price, u.name as unit_name  
				from t_goods g, t_goods_unit u
				where g.bar_code = '%s' and g.unit_id = u.id ";
		$data = $db->query($sql, $barcode);
		
		if (! $data) {
			$result["success"] = false;
			$result["msg"] = "条码为[{$barcode}]的商品不存在";
		} else {
			$result["success"] = true;
			$result["id"] = $data[0]["id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["salePrice"] = $data[0]["sale_price"];
			$result["unitName"] = $data[0]["unit_name"];
		}
		
		return $result;
	}

	/**
	 * 通过条形码查询商品信息, 采购入库单使用
	 */
	public function queryGoodsInfoByBarcodeForPW($params) {
		$barcode = $params["barcode"];
		
		$result = array();
		
		$db = M();
		$sql = "select g.id, g.code, g.name, g.spec, g.purchase_price, u.name as unit_name  
				from t_goods g, t_goods_unit u
				where g.bar_code = '%s' and g.unit_id = u.id ";
		$data = $db->query($sql, $barcode);
		
		if (! $data) {
			$result["success"] = false;
			$result["msg"] = "条码为[{$barcode}]的商品不存在";
		} else {
			$result["success"] = true;
			$result["id"] = $data[0]["id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["purchasePrice"] = $data[0]["purchase_price"];
			$result["unitName"] = $data[0]["unit_name"];
		}
		
		return $result;
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
		
		$result = array();
		
		$id = $params["id"];
		
		$db = M();
		$sql = "select name, parent_id 
				from t_goods_brand
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $result;
		}
		
		$result["name"] = $data[0]["name"];
		$parentId = $data[0]["parent_id"];
		$result["parentBrandId"] = $parentId;
		if ($parentId) {
			$sql = "select full_name 
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if ($data) {
				$result["parentBrandName"] = $data[0]["full_name"];
			} else {
				$result["parentBrandId"] = null;
				$result["parentBrandName"] = null;
			}
		} else {
			$result["parentBrandName"] = null;
		}
		
		return $result;
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
		
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			$db->rollback();
			return $this->bad("要删除的品牌不存在");
		}
		$fullName = $data[0]["full_name"];
		
		$sql = "select count(*) as cnt from t_goods
				where brand_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("品牌[$fullName]已经在商品中使用，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_goods_brand where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			$db->rollback();
			return $this->bad("品牌[$fullName]还有子品牌，所以不能被删除");
		}
		
		$sql = "delete from t_goods_brand where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			$db->rollback();
			return $this->sqlError(__LINE__);
		}
		
		$log = "删除商品品牌[$fullName]";
		$bs = new BizlogService();
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