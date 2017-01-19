<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 商品DAO
 *
 * @author 李静波
 */
class GoodsDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 商品列表
	 */
	public function goodsList($params) {
		$db = $this->db;
		
		$categoryId = $params["categoryId"];
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$start = $params["start"];
		$limit = $params["limit"];
		
		$loginUserId = $params["loginUserId"];
		
		$result = array();
		$sql = "select g.id, g.code, g.name, g.sale_price, g.spec,  g.unit_id, u.name as unit_name,
					g.purchase_price, g.bar_code, g.memo, g.data_org, g.brand_id
				from t_goods g, t_goods_unit u
				where (g.unit_id = u.id) and (g.category_id = '%s') ";
		$queryParam = array();
		$queryParam[] = $categoryId;
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", $loginUserId);
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
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS, "g", $loginUserId);
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

	private function getBrandFullNameById($db, $brandId) {
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $brandId);
		if ($data) {
			return $data[0]["full_name"];
		} else {
			return null;
		}
	}

	/**
	 * 新增商品
	 */
	public function addGoods($params) {
		$db = $this->db;
		
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
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		$py = $params["py"];
		$specPY = $params["specPY"];
		
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
		
		$sql = "insert into t_goods (id, code, name, spec, category_id, unit_id, sale_price,
						py, purchase_price, bar_code, memo, data_org, company_id, spec_py, brand_id)
					values ('%s', '%s', '%s', '%s', '%s', '%s', %f, '%s', %f, '%s', '%s', '%s', '%s', '%s',
						if('%s' = '', null, '%s'))";
		$rc = $db->execute($sql, $id, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
				$purchasePrice, $barCode, $memo, $dataOrg, $companyId, $specPY, $brandId, $brandId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑商品
	 */
	public function updateGoods($params) {
		$db = $this->db;
		
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
		
		$py = $params["py"];
		$specPY = $params["specPY"];
		
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
		
		$sql = "update t_goods
				set code = '%s', name = '%s', spec = '%s', category_id = '%s',
				    unit_id = '%s', sale_price = %f, py = '%s', purchase_price = %f,
					bar_code = '%s', memo = '%s', spec_py = '%s',
					brand_id = if('%s' = '', null, '%s')
				where id = '%s' ";
		
		$rc = $db->execute($sql, $code, $name, $spec, $categoryId, $unitId, $salePrice, $py, 
				$purchasePrice, $barCode, $memo, $specPY, $brandId, $brandId, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	public function getGoodsById($id) {
		$db = $this->db;
		
		$sql = "select code, name, spec from t_goods where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return array(
					"code" => $data[0]["code"],
					"name" => $data[0]["name"],
					"spec" => $data[0]["spec"]
			);
		} else {
			return null;
		}
	}

	/**
	 * 删除商品
	 */
	public function deleteGoods($params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		
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
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 商品字段，查询数据
	 */
	public function queryData($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BILL, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code
				limit 20";
		$data = $db->query($sql, $queryParams);
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
	 */
	public function queryDataWithSalePrice($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BILL, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code
				limit 20";
		$data = $db->query($sql, $queryParams);
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
	public function queryDataWithPurchasePrice($params) {
		$db = $this->db;
		
		$queryKey = $params["queryKey"];
		$loginUserId = $params["loginUserId"];
		
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
		
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_BILL, "g", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParams = array_merge($queryParams, $rs[1]);
		}
		
		$sql .= " order by g.code
				limit 20";
		$data = $db->query($sql, $queryParams);
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
	public function getGoodsInfo($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$categoryId = $params["categoryId"];
		
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
	 * 通过条形码查询商品信息, 销售出库单使用
	 */
	public function queryGoodsInfoByBarcode($params) {
		$db = $this->db;
		
		$barcode = $params["barcode"];
		
		$result = array();
		
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
		$db = $this->db;
		
		$barcode = $params["barcode"];
		
		$result = array();
		
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
}