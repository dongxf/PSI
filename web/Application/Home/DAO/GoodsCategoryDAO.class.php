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

	public function getGoodsCategoryById($id) {
		$db = $this->db;
		
		$sql = "select code, name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if ($data) {
			return array(
					"code" => $data[0]["code"],
					"name" => $data[0]["name"]
			);
		} else {
			return null;
		}
	}

	/**
	 * 新增商品分类
	 */
	public function addGoodsCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查同编码的分类是否存在
		$sql = "select count(*) as cnt from t_goods_category where code = '%s' ";
		$data = $db->query($sql, $code);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的分类已经存在");
		}
		
		if ($parentId) {
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $parentId);
			$fullName = "";
			if ($data) {
				$fullName = $data[0]["full_name"];
				$fullName .= "\\" . $name;
			}
			
			$sql = "insert into t_goods_category (id, code, name, data_org, parent_id,
							full_name, company_id)
						values ('%s', '%s', '%s', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $code, $name, $dataOrg, $parentId, $fullName, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "insert into t_goods_category (id, code, name, data_org, full_name, company_id)
					values ('%s', '%s', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $code, $name, $dataOrg, $name, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 同步子分类的full_name字段
	 */
	private function updateSubCategoryFullName($db, $id) {
		$sql = "select full_name from t_goods_category where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return true;
		}
		
		$fullName = $data[0]["full_name"];
		$sql = "select id, name from t_goods_category where parent_id = '%s' ";
		$data = $db->query($sql, $id);
		foreach ( $data as $v ) {
			$subId = $v["id"];
			$name = $v["name"];
			
			$subFullName = $fullName . "\\" . $name;
			$sql = "update t_goods_category
					set full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $subFullName, $subId);
			if ($rc === false) {
				return false;
			}
			
			$rc = $this->updateSubCategoryFullName($db, $subId); // 递归调用自身
			if ($rc === false) {
				return false;
			}
		}
		
		return true;
	}

	/**
	 * 编辑商品分类
	 */
	public function updateGoodsCategory($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$code = $params["code"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		
		// 检查同编码的分类是否存在
		$sql = "select count(*) as cnt from t_goods_category where code = '%s' and id <> '%s' ";
		$data = $db->query($sql, $code, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("编码为 [{$code}] 的分类已经存在");
		}
		
		if ($parentId) {
			if ($parentId == $id) {
				return $this->bad("上级分类不能是自身");
			}
			
			$tempParentId = $parentId;
			while ( $tempParentId != null ) {
				$sql = "select parent_id from t_goods_category where id = '%s' ";
				$d = $db->query($sql, $tempParentId);
				if ($d) {
					$tempParentId = $d[0]["parent_id"];
					
					if ($tempParentId == $id) {
						return $this->bad("不能选择下级分类作为上级分类");
					}
				} else {
					$tempParentId = null;
				}
			}
			
			$sql = "select full_name from t_goods_category where id = '%s' ";
			$data = $db->query($sql, $parentId);
			$fullName = $name;
			if ($data) {
				$fullName = $data[0]["full_name"] . "\\" . $name;
			}
			
			$sql = "update t_goods_category
					set code = '%s', name = '%s', parent_id = '%s', full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $code, $name, $parentId, $fullName, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "update t_goods_category
					set code = '%s', name = '%s', parent_id = null, full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $code, $name, $name, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 同步子分类的full_name字段
		$rc = $this->updateSubCategoryFullName($db, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}
}