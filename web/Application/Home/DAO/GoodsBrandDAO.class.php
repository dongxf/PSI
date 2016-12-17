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
}