<?php

namespace Home\Service;

/**
 * 商品Service
 *
 * @author 李静波
 */
class GoodsService extends PSIBaseService {

	public function allUnits() {
		return M()->query("select id, name from t_goods_unit order by name");
	}

	public function editUnit($params) {
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
		$id = $params["id"];

		$db = M();
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
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

	public function allCategories() {
		$sql = "select c.id, c.code, c.name, count(g.id) as cnt "
				. " from t_goods_category c"
				. " left join t_goods g "
				. " on c.id = g.category_id "
				. " group by c.id "
				. " order by c.code";
		
		return M()->query($sql);
	}

	public function editCategory($params) {
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

			$sql = "update t_goods_category"
					. " set code = '%s', name = '%s' "
					. " where id = '%s' ";
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

			$sql = "insert into t_goods_category (id, code, name) values ('%s', '%s', '%s')";
			$db->execute($sql, $id, $code, $name);

			$log = "新增商品分类: 编码 = {$code}， 分类名称 = {$name}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		}
		return $this->ok($id);
	}

	public function deleteCategory($params) {
		$id = $params["id"];

		$db = M();
		$sql = "select code, name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
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
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];

		$db = M();
		$result = array();
		$sql = "select g.id, g.code, g.name, g.sale_price, g.spec,  g.unit_id, u.name as unit_name"
				. " from t_goods g, t_goods_unit u "
				. " where g.unit_id = u.id and category_id = '%s' "
				. " order by g.code "
				. " limit " . $start . ", " . $limit;
		$data = $db->query($sql, $categoryId);

		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["salePrice"] = $v["sale_price"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitId"] = $v["unit_id"];
			$result[$i]["unitName"] = $v["unit_name"];
		}

		$sql = "select count(*) as cnt from t_goods where category_id = '%s' ";
		$data = $db->query($sql, $categoryId);
		$totalCount = $data[0]["cnt"];
		
		return array("goodsList" => $result, "totalCount" => $totalCount);
	}

	public function editGoods($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$categoryId = $params["categoryId"];
		$unitId = $params["unitId"];
		$salePrice = $params["salePrice"];

		$db = M();
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $unitId);
		if (!$data) {
			return $this->bad("计量单位不存在");
		}
		$sql = "select name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		if (!$data) {
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

			$ps = new PinyinService();
			$py = $ps->toPY($name);

			$sql = "update t_goods"
					. " set code = '%s', name = '%s', spec = '%s', category_id = '%s', "
					. "       unit_id = '%s', sale_price = %f, py = '%s' "
					. " where id = '%s' ";

			$db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, $id);

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

			$idGen = new IdGenService();
			$id = $idGen->newId();
			$ps = new PinyinService();
			$py = $ps->toPY($name);

			$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price, py)"
					. " values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s')";
			$db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py);

			$log = "新增商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		}

		return $this->ok($id);
	}

	/**
	 * 编辑商品（双单位，TU : Two Units)
	 */
	public function editGoodsTU($params) {
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$categoryId = $params["categoryId"];
		$unitId = $params["unitId"];
		$salePrice = $params["salePrice"];
		$purchaseUnitId = $params["purchaseUnitId"];
		$purchasePrice = $params["purchasePrice"];
		$psFactor = $params["psFactor"];

		if (floatval($psFactor) <= 0) {
			return $this->bad("采购/销售计量单位转换比例必须大于0");
		}
		
		$db = M();
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $purchaseUnitId);
		if (!$data) {
			return $this->bad("采购计量单位不存在");
		}
		
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $unitId);
		if (!$data) {
			return $this->bad("销售计量单位不存在");
		}
		$sql = "select name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $categoryId);
		if (!$data) {
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

			$ps = new PinyinService();
			$py = $ps->toPY($name);

			$sql = "update t_goods"
					. " set code = '%s', name = '%s', spec = '%s', category_id = '%s', "
					. "       unit_id = '%s', sale_price = %f, py = '%s',
							purchase_unit_id = '%s', purchase_price = %f, ps_factor = %f "
					. " where id = '%s' ";

			$db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
					$purchaseUnitId, $purchasePrice, $psFactor, $id);

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

			$idGen = new IdGenService();
			$id = $idGen->newId();
			$ps = new PinyinService();
			$py = $ps->toPY($name);

			$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price, py,
					 purchase_unit_id, purchase_price, ps_factor)"
					. " values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', '%s', %f, %f)";
			$db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py,
					$purchaseUnitId, $purchasePrice, $psFactor);

			$log = "新增商品: 商品编码 = {$code}, 品名 = {$name}, 规格型号 = {$spec}";
			$bs = new BizlogService();
			$bs->insertBizlog($log, "基础数据-商品");
		}

		return $this->ok($id);
	}
	
	public function deleteGoods($params) {
		$id = $params["id"];

		$db = M();
		$sql = "select code, name, spec from t_goods where id = '%s' ";
		$data = $db->query($sql, $id);
		if (!$data) {
			return $this->bad("要删除的商品不存在");
		}
		$code = $data[0]["code"];
		$name = $data[0]["name"];
		$spec = $data[0]["spec"];

		// 判断商品是否能删除
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
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name"
				. " from t_goods g, t_goods_unit u"
				. " where (g.unit_id = u.id)"
				. "    and (g.code like '%s' or g.name like '%s' or g.py like '%s') "
				. " order by g.code "
				. " limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
		$result = array();
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
		}
		
		return $result;
	}
	public function queryDataWithSalePrice($queryKey) {
		if ($queryKey == null) {
			$queryKey = "";
		}
		
		$sql = "select g.id, g.code, g.name, g.spec, u.name as unit_name, g.sale_price"
				. " from t_goods g, t_goods_unit u"
				. " where (g.unit_id = u.id)"
				. "    and (g.code like '%s' or g.name like '%s' or g.py like '%s') "
				. " order by g.code "
				. " limit 20";
		$key = "%{$queryKey}%";
		$data = M()->query($sql, $key, $key, $key);
		$result = array();
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["salePrice"] = $v["sale_price"];
		}
		
		return $result;
	}
	
	public function goodsListTU($params) {
		$categoryId = $params["categoryId"];
		$page = $params["page"];
		$start = $params["start"];
		$limit = $params["limit"];
	
		$db = M();
		$result = array();
		$sql = "select g.id, g.code, g.name, g.sale_price, g.spec,  
					g.unit_id, u.name as unit_name, u2.name as purchase_unit_name,
				    u2.id as purchase_unit_id, g.purchase_price, g.ps_factor
				 from t_goods g
				 left join t_goods_unit u
				 on g.unit_id = u.id 
				 left join t_goods_unit u2
				 on g.purchase_unit_id = u2.id
				 where g.category_id = '%s'
				 order by g.code 
				 limit " . $start . ", " . $limit;
		$data = $db->query($sql, $categoryId);
	
		foreach ($data as $i => $v) {
			$result[$i]["id"] = $v["id"];
			$result[$i]["code"] = $v["code"];
			$result[$i]["name"] = $v["name"];
			$result[$i]["salePrice"] = $v["sale_price"];
			$result[$i]["spec"] = $v["spec"];
			$result[$i]["unitId"] = $v["unit_id"];
			$result[$i]["unitName"] = $v["unit_name"];
			$result[$i]["purchaseUnitId"] = $v["purchase_unit_id"];
			$result[$i]["purchaseUnitName"] = $v["purchase_unit_name"];
			$result[$i]["purchasePrice"] = $v["purchase_price"];
			$result[$i]["psFactor"] = $v["ps_factor"];
		}
	
		$sql = "select count(*) as cnt from t_goods where category_id = '%s' ";
		$data = $db->query($sql, $categoryId);
		$totalCount = $data[0]["cnt"];
	
		return array("goodsList" => $result, "totalCount" => $totalCount);
	}
	
	public function getGoodsInfo($id) {
		$sql = "select category_id, code, name, spec, unit_id, sale_price
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
				
			return $result;
		} else {
			return array();
		}
	}
	
	public function getGoodsInfoTU($id) {
		$sql = "select category_id, code, name, spec, unit_id, sale_price, 
				   purchase_unit_id, purchase_price, ps_factor
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
			$result["purchaseUnitId"] = $data[0]["purchase_unit_id"];
			$result["purchasePrice"] = $data[0]["purchase_price"];
			$result["psFactor"] = $data[0]["ps_factor"];
				
			return $result;
		} else {
			return array();
		}
	}
}