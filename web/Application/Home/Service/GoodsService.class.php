<?php

namespace Home\Service;

use Home\Common\FIdConst;

/**
 * 商品Service
 *
 * @author 李静波
 */
class GoodsService extends PSIBaseService {

	public function allUnits() {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		return M()->query("select id, name from t_goods_unit order by name");
	}

	public function editUnit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$name = $params["name"];
		
		$db = M();
		
		if ($id) {
			// 编辑
			// 检查计量单位是否存在
			$sql = "select count(*) as cnt from t_goods_unit where name = '%s' and id <> '%s' ";
			$data = $db->query($sql, $name, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("计量单位 [$name] 已经存在");
			}
			
			$sql = "update t_goods_unit set name = '%s' where id = '%s' ";
			$db->execute($sql, $name, $id);
			
			$log = "编辑计量单位: $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品计量单位");
		} else {
			// 新增
			// 检查计量单位是否存在
			$sql = "select count(*) as cnt from t_goods_unit where name = '%s' ";
			$data = $db->query($sql, $name);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("计量单位 [$name] 已经存在");
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$sql = "insert into t_goods_unit(id, name) values ('%s', '%s') ";
			$db->execute($sql, $id, $name);
			
			$log = "新增计量单位: $name";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品计量单位");
		}
		
		return $this->ok($id);
	}

	public function deleteUnit($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的商品计量单位不存在");
		}
		$name = $data[0]["name"];
		
		// 检查记录单位是否被使用
		$sql = "select count(*) as cnt from t_goods where unit_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品计量单位 [$name] 已经被使用，不能删除");
		}
		
		$sql = "delete from t_goods_unit where id = '%s' ";
		$db->execute($sql, $id);
		
		$log = "删除商品计量单位: $name";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-商品计量单位");
		
		return $this->ok();
	}

	public function allCategories($params) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$sql = "select c.id, c.code, c.name, count(g.id) as cnt 
				from t_goods_category c
				left join t_goods g 
				on (c.id = g.category_id) ";
		$queryParam = array();
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
		
		$p = array();
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "c", $p);
		if ($rs) {
			$sql .= " where " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " group by c.id 
				  order by c.code";
		
		return M()->query($sql, $queryParam);
	}

	public function editCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		
		$db = M();
		
		if ($id) {
			// 编辑
			// 检查同编码的分类是否存在
			$sql = "select count(*) as cnt from t_goods_category where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的分类已经存在");
			}
			
			$sql = "update t_goods_category
					set code = '%s', name = '%s' 
					where id = '%s' ";
			$db->execute($sql, $code, $name, $id);
			
			$log = "编辑商品分类: 编码 = {$code}， 分类名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		} else {
			// 新增
			// 检查同编码的分类是否存在
			$sql = "select count(*) as cnt from t_goods_category where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}] 的分类已经存在");
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			$sql = "insert into t_goods_category (id, code, name, data_org) 
					values ('%s', '%s', '%s', '%s')";
			$db->execute($sql, $id, $code, $name, $dataOrg);
			
			$log = "新增商品分类: 编码 = {$code}， 分类名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		}
		return $this->ok($id);
	}

	public function deleteCategory($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$sql = "select code, name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要删除的商品分类不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];
		
		$sql = "select count(*) as cnt from t_goods where category_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("还有属于商品分类 [{$name}] 的商品，不能删除该分类");
		}
		
		$sql = "delete from t_goods_category where id = '%s' ";
		$db->execute($sql, $id);
		
		$log = "删除商品分类：  编码 = {$code}， 分类名称 = {$name}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-商品");
		
		return $this->ok();
	}

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
					g.purchase_price, g.bar_code, g.memo
				from t_goods g, t_goods_unit u 
				where (g.unit_id = u.id) and (g.category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", array());
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
		}
		
		$sql = "select count(*) as cnt from t_goods g where (g.category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
		$ds = new DataOrgService();
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", array());
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
		
		$db = M();
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $unitId);
		if (! $data) {
			return $this->bad("计量单位不存在");
		}
		$sql = "select name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		if (! $data) {
			return $this->bad("商品分类不存在");
		}
		
		if ($id) {
			// 编辑
			// 检查商品编码是否唯一
			$sql = "select count(*) as cnt from t_goods where code = '%s' and id <> '%s' ";
			$data = $db->query($sql, $code, $id);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}]的商品已经存在");
			}
			
			// 如果录入了条形码，则需要检查条形码是否唯一
			if ($barCode) {
				$sql = "select count(*) as cnt from t_goods where bar_code = '%s' and id <> '%s' ";
				$data = $db->query($sql, $barCode, $id);
				$cnt = $data[0]["cnt"];
				if ($cnt != 0) {
					return $this->bad("条形码[{$barCode}]已经被其他商品使用");
				}
			}
			
			$ps = new PinyinService();
			$py = $ps->toPY($name);
			
			$sql = "update t_goods
					set code = '%s', name = '%s', spec = '%s', category_id = '%s', 
					    unit_id = '%s', sale_price = %f, py = '%s', purchase_price = %f,
						bar_code = '%s', memo = '%s'
					where id = '%s' ";
			
			$db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
					$purchasePrice, $barCode, $memo, $id);
			
			$log = "编辑商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		} else {
			// 新增
			// 检查商品编码是否唯一
			$sql = "select count(*) as cnt from t_goods where code = '%s' ";
			$data = $db->query($sql, $code);
			$cnt = $data[0]["cnt"];
			if ($cnt > 0) {
				return $this->bad("编码为 [{$code}]的商品已经存在");
			}
			
			// 如果录入了条形码，则需要检查条形码是否唯一
			if ($barCode) {
				$sql = "select count(*) as cnt from t_goods where bar_code = '%s' ";
				$data = $db->query($sql, $barCode);
				$cnt = $data[0]["cnt"];
				if ($cnt != 0) {
					return $this->bad("条形码[{$barCode}]已经被其他商品使用");
				}
			}
			
			$idGen = new IdGenService();
			$id = $idGen->newId();
			$ps = new PinyinService();
			$py = $ps->toPY($name);
			$us = new UserService();
			$dataOrg = $us->getLoginUserDataOrg();
			
			$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price, 
						py, purchase_price, bar_code, memo, data_org)
					values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', %f, '%s', '%s', '%s')";
			$db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
					$purchasePrice, $barCode, $memo, $dataOrg);
			
			$log = "新增商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		}
		
		return $this->ok($id);
	}

	public function deleteGoods($params) {
		if ($this->isNotOnline()) {
			return $this->notOnlineError();
		}
		
		$id = $params["id"];
		
		$db = M();
		$sql = "select code, name, spec from t_goods where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
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
			return $this->bad("商品[{$code} {$name}]已经在采购订单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_pw_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]已经在采购入库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_ws_bill_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]已经在销售出库单中使用了，不能删除");
		}
		
		$sql = "select count(*) as cnt from t_inventory_detail where goods_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品[{$code} {$name}]在业务中已经使用了，不能删除");
		}
		
		$sql = "delete from t_goods where id = '%s' ";
		$db->execute($sql, $id);
		
		$log = "删除商品： 商品编码 = {$code}， 品名 = {$name}，规格型号 = {$spec}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-商品");
		
		return $this->ok();
	}

	public function queryData($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s') 
				order by g.code 
				limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
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

	public function queryDataWithSalePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name, g.sale_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s') 
				order by g.code 
				limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
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

	public function queryDataWithPurchasePrice($queryKey) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name, g.purchase_price, g.memo
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id)
				and (g.code like '%s' or g.name like '%s' or g.py like '%s') 
				order by g.code 
				limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
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

	public function getGoodsInfo($id) {
		if ($this->isNotOnline()) {
			return $this->emptyResult();
		}
		
		$sql = "select category_id, code, name, spec, unit_id, sale_price, purchase_price, 
					bar_code, memo
				from t_goods
				where id = '%s' ";
		$data = M()->query($sql, $id);
		if ($data) {
			$result = array();
			$result["categoryId"] = $data[0]["category_id"];
			$result["code"] = $data[0]["code"];
			$result["name"] = $data[0]["name"];
			$result["spec"] = $data[0]["spec"];
			$result["unitId"] = $data[0]["unit_id"];
			$result["salePrice"] = $data[0]["sale_price"];
			
			$v = $data[0]["purchase_price"];
			if ($v == 0) {
				$result["purchasePrice"] = null;
			} else {
				$result["purchasePrice"] = $v;
			}
			
			$result["barCode"] = $data[0]["bar_code"];
			$result["memo"] = $data[0]["memo"];
			
			return $result;
		} else {
			return array();
		}
	}

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
		
		$sql = "select w.id as warehouse_id, w.code as warehouse_code, w.name as warehouse_name,
					s.safety_inventory, s.inventory_upper
				from t_warehouse w
				left join t_goods_si s
				on w.id = s.warehouse_id and s.goods_id = '%s'
				where w.inited = 1
				order by w.code";
		$data = $db->query($sql, $id);
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
		try {
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
			$db->execute($sql, $id);
			
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
				if (! $rc) {
					$db->rollback();
					return $this->sqlError();
				}
			}
			
			$bs = new BizlogService();
			$log = "为商品[$goodsCode $goodsName $goodsSpec]设置安全库存";
			$bs->insertBizlog($log, "基础数据-商品");
			
			$db->commit();
		} catch ( Exception $e ) {
			$db->rollback();
			return $this->sqlError();
		}
		
		return $this->ok();
	}

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
}