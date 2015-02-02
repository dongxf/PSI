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
		$sql = "select count(*) from t_goods where unit_id = '%s' ";
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
		return M()->query("select id, code, name from t_goods_category order by code");
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

		$sql = "select count(*) from t_goods where category_id = '%s' ";
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

		// TODO : 判断商品是否能删除

		$sql = "delete from t_goods where id = '%s' ";
		$db->execute($sql, $id);

		$log = "删除商品： 商品编码 = {$code}， 品名 = {$name}，规格型号 = {$spec}";
		$bs = new BizlogService();
		$bs->insertBizlog($log, "基础数据-商品");

		return $this->ok();
	}

	public function queryData($queryKey) {
		if (!queryKey) {
			return array();
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
		if (!queryKey) {
			return array();
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
}