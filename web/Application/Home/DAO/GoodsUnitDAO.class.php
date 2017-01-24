<?php

namespace Home\DAO;

/**
 * 商品计量单位 DAO
 *
 * @author 李静波
 */
class GoodsUnitDAO extends PSIBaseExDAO {

	/**
	 * 返回所有商品计量单位
	 */
	public function allUnits() {
		$db = $this->db;
		
		$sql = "select id, name
				from t_goods_unit
				order by convert(name USING gbk) collate gbk_chinese_ci";
		
		return $db->query($sql);
	}

	/**
	 * 新增商品计量单位
	 */
	public function addUnit(& $params) {
		$db = $this->db;
		
		$name = $params["name"];
		
		// 检查计量单位是否存在
		$sql = "select count(*) as cnt from t_goods_unit where name = '%s' ";
		$data = $db->query($sql, $name);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("计量单位 [$name] 已经存在");
		}
		
		$dataOrg = $params["dataOrg"];
		$companyId = $params["companyId"];
		if ($this->dataOrgNotExists($dataOrg)) {
			return $this->badParam("dataOrg");
		}
		if ($this->companyIdNotExists($companyId)) {
			return $this->badParam("companyId");
		}
		
		$id = $this->newId();
		$params["id"] = $id;
		
		$sql = "insert into t_goods_unit(id, name, data_org, company_id)
					values ('%s', '%s', '%s', '%s') ";
		$rc = $db->execute($sql, $id, $name, $dataOrg, $companyId);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 编辑商品计量单位
	 */
	public function updateUnit(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		$name = $params["name"];
		
		// 检查计量单位是否存在
		$sql = "select count(*) as cnt from t_goods_unit where name = '%s' and id <> '%s' ";
		$data = $db->query($sql, $name, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("计量单位 [$name] 已经存在");
		}
		
		$sql = "update t_goods_unit set name = '%s' where id = '%s' ";
		$rc = $db->execute($sql, $name, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		// 操作成功
		return null;
	}

	/**
	 * 通过id查询商品计量单位
	 */
	public function getGoodsUnitById($id) {
		$db = $this->db;
		
		$sql = "select name from t_goods_unit where id = '%s' ";
		$data = $db->query($sql, $id);
		if (! $data) {
			return null;
		} else {
			return array(
					"name" => $data[0]["name"]
			);
		}
	}

	/**
	 * 删除商品计量单位
	 */
	public function deleteUnit(& $params) {
		$db = $this->db;
		
		$id = $params["id"];
		
		$goodsUnit = $this->getGoodsUnitById($id);
		if (! $goodsUnit) {
			return $this->bad("要删除的商品计量单位不存在");
		}
		
		$name = $goodsUnit["name"];
		
		// 检查记录单位是否被使用
		$sql = "select count(*) as cnt from t_goods where unit_id = '%s' ";
		$data = $db->query($sql, $id);
		$cnt = $data[0]["cnt"];
		if ($cnt > 0) {
			return $this->bad("商品计量单位 [$name] 已经被使用，不能删除");
		}
		
		$sql = "delete from t_goods_unit where id = '%s' ";
		$rc = $db->execute($sql, $id);
		if ($rc === false) {
			return $this->sqlError(__METHOD__, __LINE__);
		}
		
		$params["name"] = $name;
		
		// 操作成功
		return null;
	}
}