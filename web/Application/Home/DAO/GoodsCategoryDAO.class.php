<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 商品分类 DAO
 *
 * @author 李静波
 */
class GoodsCategoryDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	private function allCategoriesInternal($db, $parentId, $rs, $params) {
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$result = array();
		$sql = "select id, code, name, full_name
				from t_goods_category c
				where (parent_id = '%s')
				";
		$queryParam = array();
		$queryParam[] = $parentId;
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code";
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $v["id"];
			$result[$i]["text"] = $v["name"];
			$result[$i]["code"] = $v["code"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			$result[$i]["fullName"] = $fullName;
			
			$children = $this->allCategoriesInternal($db, $id, $rs, $params); // 自身递归调用
			
			$result[$i]["children"] = $children;
			$result[$i]["leaf"] = count($children) == 0;
			$result[$i]["expanded"] = true;
			
			$result[$i]["cnt"] = $this->getGoodsCountWithAllSub($db, $id, $params, $rs);
		}
		
		return $result;
	}

	/**
	 * 获得某个商品分类及其所属子分类下的所有商品的种类数
	 */
	private function getGoodsCountWithAllSub($db, $categoryId, $params, $rs) {
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$sql = "select count(*) as cnt 
					from t_goods c
					where c.category_id = '%s' ";
		$queryParam = array();
		$queryParam[] = $categoryId;
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
		$result = $data[0]["cnt"];
		
		// 子分类
		$sql = "select id
				from t_goods_category c
				where (parent_id = '%s')
				";
		$queryParam = array();
		$queryParam[] = $categoryId;
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $v ) {
			// 递归调用自身
			$result += $this->getGoodsCountWithAllSub($db, $v["id"], $params, $rs);
		}
		return $result;
	}

	/**
	 * 返回所有的商品分类
	 */
	public function allCategories($params) {
		$db = $this->db;
		
		$code = $params["code"];
		$name = $params["name"];
		$spec = $params["spec"];
		$barCode = $params["barCode"];
		
		$loginUserId = $params["loginUserId"];
		
		$sql = "select id, code, name, full_name
				from t_goods_category c
				where (parent_id is null)
				";
		$queryParam = array();
		$ds = new DataOrgDAO($db);
		$rs = $ds->buildSQL(FIdConst::GOODS_CATEGORY, "c", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by code";
		
		$data = $db->query($sql, $queryParam);
		$result = array();
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $v["id"];
			$result[$i]["text"] = $v["name"];
			$result[$i]["code"] = $v["code"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			$result[$i]["fullName"] = $fullName;
			
			$children = $this->allCategoriesInternal($db, $id, $rs, $params);
			
			$result[$i]["children"] = $children;
			$result[$i]["leaf"] = count($children) == 0;
			$result[$i]["expanded"] = true;
			
			$result[$i]["cnt"] = $this->getGoodsCountWithAllSub($db, $id, $params, $rs);
		}
		
		return $result;
	}
}