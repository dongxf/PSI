<?php

namespace Home\DAO;

use Home\Common\FIdConst;

/**
 * 商品品牌 DAO
 *
 * @author 李静波
 */
class GoodsBrandDAO extends PSIBaseDAO {
	var $db;

	function __construct($db = null) {
		if ($db == null) {
			$db = M();
		}
		
		$this->db = $db;
	}

	/**
	 * 用递归调用的方式查询所有品牌
	 */
	private function allBrandsInternal($db, $parentId, $rs) {
		$result = array();
		$sql = "select id, name, full_name
				from t_goods_brand b
				where (parent_id = '%s')
				";
		$queryParam = array();
		$queryParam[] = $parentId;
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by name";
		$data = $db->query($sql, $queryParam);
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $v["id"];
			$result[$i]["text"] = $v["name"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			$result[$i]["fullName"] = $fullName;
			
			$children = $this->allBrandsInternal($db, $id, $rs); // 自身递归调用
			
			$result[$i]["children"] = $children;
			$result[$i]["leaf"] = count($children) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}

	/**
	 * 获得所有的品牌
	 */
	public function allBrands($params) {
		$db = $this->db;
		
		$loginUserId = $params["loginUserId"];
		
		$result = array();
		$sql = "select id, name, full_name
				from t_goods_brand b
				where (parent_id is null)
				";
		$queryParam = array();
		$ds = new DataOrgDAO();
		$rs = $ds->buildSQL(FIdConst::GOODS_BRAND, "b", $loginUserId);
		if ($rs) {
			$sql .= " and " . $rs[0];
			$queryParam = array_merge($queryParam, $rs[1]);
		}
		
		$sql .= " order by name";
		
		$data = $db->query($sql, $queryParam);
		$result = array();
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$result[$i]["id"] = $id;
			$result[$i]["text"] = $v["name"];
			$fullName = $v["full_name"];
			if (! $fullName) {
				$fullName = $v["name"];
			}
			$result[$i]["fullName"] = $fullName;
			
			$children = $this->allBrandsInternal($db, $id, $rs);
			
			$result[$i]["children"] = $children;
			$result[$i]["leaf"] = count($children) == 0;
			$result[$i]["expanded"] = true;
		}
		
		return $result;
	}

	/**
	 * 新增商品品牌
	 */
	public function addBrand($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查上级品牌是否存在
		$fullName = $name;
		if ($parentId) {
			$sql = "select full_name
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if (! $data) {
				return $this->bad("所选择的上级商品品牌不存在");
			}
			$fullName = $data[0]["full_name"] . "\\" . $name;
		}
		
		if ($parentId) {
			$sql = "insert into t_goods_brand(id, name, full_name, parent_id, data_org, company_id)
						values ('%s', '%s', '%s', '%s', '%s', '%s')";
			$rc = $db->execute($sql, $id, $name, $fullName, $parentId, $dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "insert into t_goods_brand(id, name, full_name, parent_id, data_org, company_id)
						values ('%s', '%s', '%s', null, '%s', '%s')";
			$rc = $db->execute($sql, $id, $name, $fullName, $dataOrg, $companyId);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 操作成功
		return null;
	}

	private function updateSubBrandsFullName($db, $parentId) {
		$sql = "select full_name from t_goods_brand where id = '%s' ";
		$data = $db->query($sql, $parentId);
		if (! $data) {
			return;
		}
		
		$parentFullName = $data[0]["full_name"];
		$sql = "select id, name
				from t_goods_brand
				where parent_id = '%s' ";
		$data = $db->query($sql, $parentId);
		foreach ( $data as $i => $v ) {
			$id = $v["id"];
			$fullName = $parentFullName . "\\" . $v["name"];
			$sql = "update t_goods_brand
					set full_name = '%s'
					where id = '%s' ";
			$db->execute($sql, $fullName, $id);
			
			// 递归调用自身
			$this->updateSubBrandsFullName($db, $id);
		}
	}

	/**
	 * 编辑商品分类
	 */
	public function updateGoodsBrand($params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		$parentId = $params["parentId"];
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		
		// 检查品牌是否存在
		$sql = "select name
				from t_goods_brand
				where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return $this->bad("要编辑的品牌不存在");
		}
		
		if ($parentId) {
			// 检查上级品牌是否存在
			$sql = "select full_name
					from t_goods_brand
					where id = '%s' ";
			$data = $db->query($sql, $parentId);
			if (! data) {
				return $this->bad("选择的上级品牌不存在");
			}
			$parentFullName = $data[0]["full_name"];
			
			// 上级品牌不能是自身
			if ($parentId == $id) {
				return $this->bad("上级品牌不能是自身");
			}
			
			// 检查下级品牌不能是作为上级品牌
			$tempParentId = $parentId;
			while ( $tempParentId != null ) {
				$sql = "select parent_id
							from t_goods_brand
							where id = '%s' ";
				$data = $db->query($sql, $tempParentId);
				if ($data) {
					$tempParentId = $data[0]["parent_id"];
				} else {
					$tempParentId = null;
				}
				
				if ($tempParentId == $id) {
					return $this->bad("下级品牌不能作为上级品牌");
				}
			}
		}
		
		if ($parentId) {
			$fullName = $parentFullName . "\\" . $name;
			$sql = "update t_goods_brand
					set name = '%s', parent_id = '%s', full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $name, $parentId, $fullName, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		} else {
			$sql = "update t_goods_brand
					set name = '%s', parent_id = null, full_name = '%s'
					where id = '%s' ";
			$rc = $db->execute($sql, $name, $name, $id);
			if ($rc === false) {
				return $this->sqlError(__METHOD__, __LINE__);
			}
		}
		
		// 同步下级品牌的full_name
		$this->updateSubBrandsFullName($db, $id);
		
		// 操作成功
		return null;
	}
}